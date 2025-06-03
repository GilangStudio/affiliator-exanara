<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Unit;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class UnitController extends Controller
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
     * Display units for a specific project.
     */
    public function index(Request $request, Project $project)
    {
        $query = $project->units();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('unit_type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $units = $query->paginate(15);

        return view('pages.superadmin.projects.units.index', compact('project', 'units'));
    }

    /**
     * Show the form for creating a new unit.
     */
    public function create(Project $project)
    {
        return view('pages.superadmin.projects.units.create', compact('project'));
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'unit_type' => 'nullable|string|max:255',
            'building_area' => 'nullable|string|max:255',
            'land_area' => 'nullable|string|max:255',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'carport' => 'nullable|integer|min:0',
            'floor' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama unit harus diisi',
            'price.required' => 'Harga harus diisi',
            'commission_type.required' => 'Tipe komisi harus dipilih',
            'commission_value.required' => 'Nilai komisi harus diisi',
        ]);

        DB::transaction(function () use ($request, $project) {
            $data = $request->only([
                'name', 'description', 'price', 'commission_type', 'commission_value',
                'unit_type', 'building_area', 'land_area', 'bedrooms', 'bathrooms', 'carport', 'floor'
            ]);
            
            $data['project_id'] = $project->id;
            $data['is_active'] = $request->boolean('is_active', true);

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('units', 'public');
            }

            $unit = Unit::create($data);

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'create_unit',
                "Unit baru dibuat untuk project {$project->name}: {$unit->name}",
                $project->id,
                ['unit_id' => $unit->id]
            );
        });

        return redirect()->route('superadmin.projects.units.index', $project)
            ->with('success', 'Unit berhasil dibuat!');
    }

    /**
     * Show the form for editing the specified unit.
     */
    public function edit(Project $project, Unit $unit)
    {
        if ($unit->project_id !== $project->id) {
            return redirect()->route('superadmin.projects.units.index', $project)
                ->with('error', 'Unit tidak ditemukan!');
        }

        return view('pages.superadmin.projects.units.edit', compact('project', 'unit'));
    }

    /**
     * Update the specified unit.
     */
    public function update(Request $request, Project $project, Unit $unit)
    {
        if ($unit->project_id !== $project->id) {
            return redirect()->route('superadmin.projects.units.index', $project)
                ->with('error', 'Unit tidak ditemukan!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'unit_type' => 'nullable|string|max:255',
            'building_area' => 'nullable|string|max:255',
            'land_area' => 'nullable|string|max:255',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'carport' => 'nullable|integer|min:0',
            'floor' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama unit harus diisi',
            'price.required' => 'Harga harus diisi',
            'commission_type.required' => 'Tipe komisi harus dipilih',
            'commission_value.required' => 'Nilai komisi harus diisi',
        ]);

        $oldData = $unit->only(['name', 'price', 'is_active']);

        $data = $request->only([
            'name', 'description', 'price', 'commission_type', 'commission_value',
            'unit_type', 'building_area', 'land_area', 'bedrooms', 'bathrooms', 'carport', 'floor'
        ]);
        
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($unit->image && Storage::disk('public')->exists($unit->image)) {
                Storage::disk('public')->delete($unit->image);
            }
            $data['image'] = $request->file('image')->store('units', 'public');
        }

        $unit->update($data);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'update_unit',
            "Unit diperbarui untuk project {$project->name}: {$unit->name}",
            $project->id,
            ['unit_id' => $unit->id, 'old_data' => $oldData, 'new_data' => $unit->only(['name', 'price', 'is_active'])]
        );

        return redirect()->route('superadmin.projects.units.index', $project)
            ->with('success', 'Unit berhasil diperbarui!');
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Project $project, Unit $unit)
    {
        if ($unit->project_id !== $project->id) {
            return redirect()->route('superadmin.projects.units.index', $project)
                ->with('error', 'Unit tidak ditemukan!');
        }

        // Check if unit has leads
        if ($unit->leads()->count() > 0) {
            return redirect()->route('superadmin.projects.units.index', $project)
                ->with('error', 'Unit tidak dapat dihapus karena masih memiliki lead.');
        }

        $unitName = $unit->name;

        // Delete image
        if ($unit->image && Storage::disk('public')->exists($unit->image)) {
            Storage::disk('public')->delete($unit->image);
        }

        $unit->delete();

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'delete_unit',
            "Unit dihapus dari project {$project->name}: {$unitName}",
            $project->id
        );

        return redirect()->route('superadmin.projects.units.index', $project)
            ->with('success', 'Unit berhasil dihapus!');
    }

    /**
     * Toggle unit status.
     */
    public function toggleStatus(Project $project, Unit $unit)
    {
        if ($unit->project_id !== $project->id) {
            return redirect()->route('superadmin.projects.units.index', $project)
                ->with('error', 'Unit tidak ditemukan!');
        }

        $newStatus = !$unit->is_active;
        $unit->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->activityLogService->log(
            Auth::id(),
            'toggle_unit_status',
            "Unit {$unit->name} {$statusText} untuk project {$project->name}",
            $project->id,
            ['unit_id' => $unit->id, 'new_status' => $newStatus]
        );

        return back()->with('success', "Unit berhasil {$statusText}!");
    }
}