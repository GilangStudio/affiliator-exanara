<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class AffiliatorController extends Controller
{
    protected $activityLogService;
    protected $notificationService;
    protected $userService;

    public function __construct(
        ActivityLogService $activityLogService,
        NotificationService $notificationService,
        UserService $userService
    ) {
        $this->activityLogService = $activityLogService;
        $this->notificationService = $notificationService;
        $this->userService = $userService;
    }

    /**
     * Display a listing of affiliators.
     */
    public function index(Request $request)
    {
        $query = User::affiliators()->with(['affiliatorProjects.project']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $affiliators = $query->paginate(15);

        return view('pages.superadmin.affiliators.index', compact('affiliators'));
    }

    /**
     * Show the form for creating a new affiliator.
     */
    public function create()
    {
        return view('pages.superadmin.affiliators.create');
    }

    /**
     * Store a newly created affiliator.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['name', 'email', 'country_code', 'phone']);
        $data['password'] = Hash::make($request->password);
        $data['role'] = 'affiliator';
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        $affiliator = User::create($data);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'create_affiliator',
            "Affiliator baru dibuat: {$affiliator->name} ({$affiliator->email})",
            null,
            ['affiliator_id' => $affiliator->id]
        );

        // Send welcome notification to affiliator
        $this->notificationService->sendWelcomeNotification($affiliator->id);

        return redirect()->route('superadmin.affiliators.index')
            ->with('success', 'Affiliator berhasil dibuat!');
    }

    /**
     * Show the form for editing the specified affiliator.
     */
    public function edit(User $affiliator)
    {
        if ($affiliator->role !== 'affiliator') {
            return redirect()->route('superadmin.affiliators.index')
                ->with('error', 'User yang dipilih bukan affiliator!');
        }

        $affiliator->load('affiliatorProjects.project');

        return view('pages.superadmin.affiliators.edit', compact('affiliator'));
    }

    /**
     * Update the specified affiliator.
     */
    public function update(Request $request, User $affiliator)
    {
        if ($affiliator->role !== 'affiliator') {
            return redirect()->route('superadmin.affiliators.index')
                ->with('error', 'User yang dipilih bukan affiliator!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $affiliator->id,
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20|unique:users,phone,' . $affiliator->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $oldData = $affiliator->only(['name', 'email', 'phone', 'is_active']);
        
        $data = $request->only(['name', 'email', 'country_code', 'phone']);
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle password update
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($affiliator->profile_photo && Storage::disk('public')->exists($affiliator->profile_photo)) {
                Storage::disk('public')->delete($affiliator->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        $affiliator->update($data);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'update_affiliator',
            "Affiliator diperbarui: {$affiliator->name}",
            null,
            ['affiliator_id' => $affiliator->id, 'old_data' => $oldData, 'new_data' => $affiliator->only(['name', 'email', 'phone', 'is_active'])]
        );

        // Send notification if status changed
        if ($oldData['is_active'] !== $affiliator->is_active) {
            $this->notificationService->sendAccountStatusNotification(
                $affiliator->id,
                $affiliator->is_active ? 'activated' : 'deactivated'
            );
        }

        return redirect()->route('superadmin.affiliators.index')
            ->with('success', 'Affiliator berhasil diperbarui!');
    }

    /**
     * Remove the specified affiliator.
     */
    public function destroy(User $affiliator)
    {
        if ($affiliator->role !== 'affiliator') {
            return redirect()->route('superadmin.affiliators.index')
                ->with('error', 'User yang dipilih bukan affiliator!');
        }

        // Check if affiliator has active projects
        $projectsCount = $affiliator->affiliatorProjects()->count();
        if ($projectsCount > 0) {
            return redirect()->route('superadmin.affiliators.index')
                ->with('error', "Affiliator tidak dapat dihapus karena masih tergabung dalam {$projectsCount} project!");
        }

        $affiliatorName = $affiliator->name;
        $affiliatorEmail = $affiliator->email;

        // Delete profile photo
        if ($affiliator->profile_photo && Storage::disk('public')->exists($affiliator->profile_photo)) {
            Storage::disk('public')->delete($affiliator->profile_photo);
        }

        // Delete affiliator
        $affiliator->delete();

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'delete_affiliator',
            "Affiliator dihapus: {$affiliatorName} ({$affiliatorEmail})"
        );

        return redirect()->route('superadmin.affiliators.index')
            ->with('success', 'Affiliator berhasil dihapus!');
    }

    /**
     * Toggle affiliator status.
     */
    public function toggleStatus(User $affiliator)
    {
        if ($affiliator->role !== 'affiliator') {
            return redirect()->route('superadmin.affiliators.index')
                ->with('error', 'User yang dipilih bukan affiliator!');
        }

        $newStatus = !$affiliator->is_active;
        $affiliator->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->activityLogService->log(
            Auth::id(),
            'toggle_affiliator_status',
            "Affiliator {$affiliator->name} {$statusText}",
            null,
            ['affiliator_id' => $affiliator->id, 'new_status' => $newStatus]
        );

        // Send notification to affiliator
        $this->notificationService->sendAccountStatusNotification(
            $affiliator->id,
            $newStatus ? 'activated' : 'deactivated'
        );

        return back()->with('success', "Affiliator berhasil {$statusText}!");
    }

    /**
     * Reset affiliator password.
     */
    public function resetPassword(Request $request, User $affiliator)
    {
        if ($affiliator->role !== 'affiliator') {
            return redirect()->route('superadmin.affiliators.index')
                ->with('error', 'User yang dipilih bukan affiliator!');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $affiliator->update([
            'password' => Hash::make($request->new_password)
        ]);

        $this->activityLogService->log(
            Auth::id(),
            'reset_affiliator_password',
            "Password affiliator {$affiliator->name} direset",
            null,
            ['affiliator_id' => $affiliator->id]
        );

        // Send notification to affiliator
        $this->notificationService->createForUser(
            $affiliator->id,
            'Password Direset',
            'Password akun Anda telah direset oleh Super Admin. Silakan login dengan password baru.',
            'warning'
        );

        return back()->with('success', 'Password affiliator berhasil direset!');
    }

    /**
     * Remove affiliator profile photo.
     */
    public function removePhoto(User $affiliator)
    {
        if ($affiliator->role !== 'affiliator') {
            return redirect()->route('superadmin.affiliators.index')
                ->with('error', 'User yang dipilih bukan affiliator!');
        }

        if ($affiliator->profile_photo && Storage::disk('public')->exists($affiliator->profile_photo)) {
            Storage::disk('public')->delete($affiliator->profile_photo);
            $affiliator->update(['profile_photo' => null]);

            $this->activityLogService->log(
                Auth::id(),
                'remove_affiliator_photo',
                "Foto profil affiliator {$affiliator->name} dihapus",
                null,
                ['affiliator_id' => $affiliator->id]
            );

            return back()->with('success', 'Foto profil berhasil dihapus!');
        }

        return back()->with('error', 'Tidak ada foto profil untuk dihapus!');
    }
}