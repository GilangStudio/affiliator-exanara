<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\ProjectRegistration;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;
use App\Services\GeneralService;

class ProjectRegistrationController extends Controller
{
    protected $activityLogService;
    protected $notificationService;

    public function __construct(
        ActivityLogService $activityLogService,
        NotificationService $notificationService
    ) {
        $this->activityLogService = $activityLogService;
        $this->notificationService = $notificationService;
    }

    /**
     * Show the project registration form
     */
    public function index()
    {
        return view('pages.project-registration');
    }

    /**
     * Store project registration
     */
    public function store(Request $request)
    {
        $request->validate([
            // Step 1 - Project Info
            'project_name' => 'required|string|max:255',
            'developer_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'description' => 'required|string',
            
            // Step 2 - Files
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brochure_file' => 'nullable|mimes:pdf|max:10240', // 10MB
            'price_list_file' => 'nullable|mimes:pdf|max:10240', // 10MB
            
            // Step 3 - Units (array validation)
            'units' => 'required|array|min:1',
            'units.*.name' => 'required|string|max:255',
            'units.*.price' => 'required|numeric|min:0',
            'units.*.unit_type' => 'required|string',
            'units.*.commission_type' => 'required|in:percentage,fixed',
            'units.*.commission_value' => 'required|numeric|min:0',
            'units.*.description' => 'nullable|string',
            'units.*.building_area' => 'nullable|numeric|min:0',
            'units.*.land_area' => 'nullable|numeric|min:0',
            'units.*.bedrooms' => 'nullable|integer|min:0',
            'units.*.bathrooms' => 'nullable|integer|min:0',
            'units.*.carport' => 'nullable|integer|min:0',
            'units.*.floor' => 'nullable|integer|min:0',
            'units.*.power_capacity' => 'nullable|integer|min:0',
            'units.*.certificate_type' => 'nullable|in:SHM,HGB,AJB',
            'units.*.unit_status' => 'required|in:ready,indent,sold_out',
            'units.*.image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Step 4 - Commission & PIC
            'commission_payment_trigger' => 'required|in:booking_fee,akad_kredit,spk',
            'pic_name' => 'required|string|max:255',
            // 'pic_phone' => 'required|string|max:20',
            // 'pic_email' => 'required|email|max:255',
            'pic_phone' => ['required','string','min:10','max:15','regex:/^08[0-9]{8,13}$/'],
            'pic_email' => ['required','email','max:255','unique:users,email'],
            
            // Step 5 - Period
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            
            // Agreements
            'data_accuracy' => 'required|accepted',
            'terms_agreement' => 'required|accepted',
        ], [
            'project_name.required' => 'Nama project harus diisi',
            'developer_name.required' => 'Nama developer harus diisi',
            'location.required' => 'Lokasi harus diisi',
            'description.required' => 'Deskripsi harus diisi',
            'logo.image' => 'Logo harus berupa gambar',
            'logo.max' => 'Ukuran logo maksimal 2MB',
            'brochure_file.mimes' => 'File brosur harus berformat PDF',
            'brochure_file.max' => 'Ukuran file brosur maksimal 10MB',
            'price_list_file.mimes' => 'File price list harus berformat PDF',
            'price_list_file.max' => 'Ukuran file price list maksimal 10MB',
            'units.required' => 'Minimal harus ada 1 unit',
            'units.min' => 'Minimal harus ada 1 unit',
            'commission_payment_trigger.required' => 'Pilih kapan komisi dibayar',
            'pic_name.required' => 'Nama PIC harus diisi',
            'pic_phone.required' => 'Phone PIC harus diisi',
            'pic_email.required' => 'Email PIC harus diisi',
            'start_date.required' => 'Tanggal mulai harus diisi',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai',
            'data_accuracy.required' => 'Anda harus menyatakan kebenaran data',
            'terms_agreement.required' => 'Anda harus menyetujui aturan program afiliasi',
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads
            $logoPath = null;
            $brochurePath = null;
            $priceListPath = null;

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('projects/logos', 'public');
            }

            if ($request->hasFile('brochure_file')) {
                $brochurePath = $request->file('brochure_file')->store('projects/brochures', 'public');
            }

            if ($request->hasFile('price_list_file')) {
                $priceListPath = $request->file('price_list_file')->store('projects/price_lists', 'public');
            }

            // Create project
            $project = Project::create([
                'name' => $request->project_name,
                'developer_name' => $request->developer_name,
                'location' => $request->location,
                'website_url' => $request->website_url,
                'description' => $request->description,
                'logo' => $logoPath,
                'brochure_file' => $brochurePath,
                'price_list_file' => $priceListPath,
                'commission_payment_trigger' => $request->commission_payment_trigger,
                'pic_name' => $request->pic_name,
                'pic_phone' => $request->pic_phone,
                'pic_email' => $request->pic_email,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'registration_type' => 'manual',
                'registration_status' => 'pending',
                'is_active' => false, // Will be activated after approval
                'terms_and_conditions' => 'Dengan mendaftar, Anda dianggap telah menyetujui syarat & ketentuan ini.',
                'require_digital_signature' => true,
            ]);

            // Create units
            foreach ($request->units as $index => $unitData) {
                $unitImagePath = null;
                
                if ($request->hasFile("units.{$index}.image")) {
                    $unitImagePath = $request->file("units.{$index}.image")->store('units/images', 'public');
                }

                Unit::create([
                    'project_id' => $project->id,
                    'name' => $unitData['name'],
                    'price' => $unitData['price'],
                    'unit_type' => $unitData['unit_type'],
                    'commission_type' => $unitData['commission_type'],
                    'commission_value' => $unitData['commission_value'],
                    'description' => $unitData['description'],
                    'building_area' => $unitData['building_area'],
                    'land_area' => $unitData['land_area'],
                    'bedrooms' => $unitData['bedrooms'],
                    'bathrooms' => $unitData['bathrooms'],
                    'carport' => $unitData['carport'],
                    'floor' => $unitData['floor'],
                    'power_capacity' => $unitData['power_capacity'],
                    'certificate_type' => $unitData['certificate_type'],
                    'unit_status' => $unitData['unit_status'],
                    'image' => $unitImagePath,
                    'is_active' => false, // Will be activated after approval
                ]);
            }

            // Create PIC user account
            $picUser = User::createPicUser(
                $request->pic_name,
                $request->pic_email,
                $request->pic_phone
            );

            // Link PIC to project
            $project->update(['pic_user_id' => $picUser->id]);

            $project->admins()->attach($picUser->id);

            // Create project registration record
            ProjectRegistration::create([
                'project_id' => $project->id,
                'submitted_by' => $picUser->id,
                'form_data' => $request->all(),
                'status' => 'pending',
            ]);

            // Log activity
            $this->activityLogService->log(
                $picUser->id,
                'submit_project_registration',
                "Mengajukan pendaftaran project: {$project->name}",
                $project->id,
                [
                    'project_name' => $project->name,
                    'developer_name' => $project->developer_name,
                    'units_count' => count($request->units),
                    'pic_email' => $request->pic_email,
                ]
            );

            // Notify superadmins
            $superAdmins = User::where('role', 'superadmin')->where('is_active', true)->get();
            
            foreach ($superAdmins as $admin) {
                $this->notificationService->createForUser(
                    $admin->id,
                    'Pendaftaran Project Baru',
                    "Project '{$project->name}' telah didaftarkan oleh " . $picUser->name . " dan menunggu persetujuan",
                    'info',
                    [
                        'project_id' => $project->id,
                        'submitter_id' => $picUser->id,
                        'action_url' => route('superadmin.projects.show', $project)
                    ]
                );
            }

            // Send confirmation to submitter
            $this->notificationService->createForUser(
                $picUser->id,
                'Pendaftaran Project Terkirim',
                "Pendaftaran project '{$project->name}' telah berhasil dikirim dan sedang menunggu persetujuan admin",
                'success',
                [
                    'project_id' => $project->id,
                    'registration_status' => 'pending'
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pendaftaran project '{$project->name}' berhasil dikirim! Silakan tunggu persetujuan admin.",
                'project_id' => $project->id,
                'redirect_url' => route('affiliator.dashboard')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded files on error
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            if ($brochurePath && Storage::disk('public')->exists($brochurePath)) {
                Storage::disk('public')->delete($brochurePath);
            }
            if ($priceListPath && Storage::disk('public')->exists($priceListPath)) {
                Storage::disk('public')->delete($priceListPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan project: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unit types for dropdown
     */
    public function getUnitTypes()
    {
        return response()->json(Unit::getUnitTypes());
    }

    /**
     * Get commission types for dropdown
     */
    public function getCommissionTypes()
    {
        return response()->json(Unit::getCommissionTypes());
    }
}