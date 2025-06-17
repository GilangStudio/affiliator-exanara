<?php

namespace App\Models;

use App\Services\GeneralService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'country_code',
        'phone',
        'profile_photo',
        'password',
        'role',
        'is_active',
        'is_pic',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_pic' => 'boolean',
        ];
    }

    // Relationships
    public function affiliatorProjects()
    {
        return $this->hasMany(AffiliatorProject::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function commissionWithdrawals()
    {
        return $this->hasMany(CommissionWithdrawal::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function adminProjects()
    {
        return $this->belongsToMany(Project::class, 'project_admins');
    }

    public function commissionHistories()
    {
        return $this->hasMany(CommissionHistory::class);
    }

    public function verifiedLeads()
    {
        return $this->hasMany(Lead::class, 'verified_by');
    }

    public function leadStatusHistories()
    {
        return $this->hasMany(LeadStatusHistory::class, 'changed_by');
    }

    /**
     * Projects where user is PIC
     */
    public function picProjects()
    {
        return $this->hasMany(Project::class, 'pic_user_id');
    }

    /**
     * Project registrations submitted by user
     */
    public function submittedRegistrations()
    {
        return $this->hasMany(ProjectRegistration::class, 'submitted_by');
    }

    /**
     * Project registrations reviewed by user
     */
    public function reviewedRegistrations()
    {
        return $this->hasMany(ProjectRegistration::class, 'reviewed_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAffiliators($query)
    {
        return $query->where('role', 'affiliator');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopePic($query)
    {
        return $query->where('is_pic', true);
    }

    public function scopeNonPic($query)
    {
        return $query->where('is_pic', false);
    }

    // Accessors
    public function getIsAffiliatorAttribute()
    {
        return $this->role === 'affiliator';
    }

    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->role === 'superadmin';
    }

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function getPhoneNumberAttribute()
    {
        return '0' . $this->phone;
    }

    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }

    /**
     * Get user role label with PIC indicator
     */
    public function getRoleLabelWithPicAttribute()
    {
        $roleLabels = [
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'affiliator' => 'Affiliator',
        ];
        
        $role = $roleLabels[$this->role] ?? ucfirst($this->role);
        
        if ($this->is_pic) {
            $role .= ' (PIC)';
        }
        
        return $role;
    }

    /**
     * Get user display name with role
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->name} ({$this->role_label_with_pic})";
    }

    // Methods
    /**
     * Check if user can manage specific project
     */
    public function canManageProject($projectId)
    {
        // Superadmin can manage all projects
        if ($this->role === 'superadmin') {
            return true;
        }
        
        // Admin can manage projects they are assigned to or PIC of
        if ($this->role === 'admin') {
            return $this->adminProjects()->where('projects.id', $projectId)->exists() ||
                   $this->picProjects()->where('id', $projectId)->exists();
        }
        
        return false;
    }

    /**
     * Get projects that user can manage
     */
    public function getManagedProjectsAttribute()
    {
        if ($this->role === 'superadmin') {
            return Project::all();
        }
        
        if ($this->role === 'admin') {
            $adminProjects = $this->adminProjects;
            $picProjects = $this->picProjects;
            
            return $adminProjects->merge($picProjects)->unique('id');
        }
        
        return collect();
    }

    /**
     * Check if user is PIC of any project
     */
    public function getHasPicProjectsAttribute()
    {
        return $this->is_pic && $this->picProjects()->exists();
    }

    /**
     * Get total commission earned by affiliator
     */
    public function getTotalCommissionEarnedAttribute()
    {
        if ($this->role !== 'affiliator') {
            return 0;
        }

        return $this->commissionHistories()
                    ->where('type', 'earned')
                    ->sum('amount');
    }

    /**
     * Get available commission balance (earned - withdrawn)
     */
    public function getAvailableCommissionBalanceAttribute()
    {
        if ($this->role !== 'affiliator') {
            return 0;
        }

        $earned = $this->commissionHistories()
                       ->where('type', 'earned')
                       ->sum('amount');
                       
        $withdrawn = $this->commissionHistories()
                          ->where('type', 'withdrawn')
                          ->sum('amount');
                          
        return $earned - $withdrawn;
    }

    /**
     * Get total leads submitted by affiliator
     */
    public function getTotalLeadsAttribute()
    {
        if ($this->role !== 'affiliator') {
            return 0;
        }

        return $this->affiliatorProjects()
                    ->with('leads')
                    ->get()
                    ->sum(function ($ap) {
                        return $ap->leads->count();
                    });
    }

    /**
     * Get verified leads count
     */
    public function getVerifiedLeadsCountAttribute()
    {
        if ($this->role !== 'affiliator') {
            return 0;
        }

        return $this->affiliatorProjects()
                    ->with('leads')
                    ->get()
                    ->sum(function ($ap) {
                        return $ap->leads->where('verification_status', 'verified')->count();
                    });
    }

    // Mutators
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = GeneralService::formatPhoneNumber($value);
    }

    // Static methods
    /**
     * Static method to create PIC user
     */
    public static function createPicUser($name, $email, $phone, $password = null)
    {
        return self::create([
            'name' => $name,
            'username' => GeneralService::processUsername($email),
            'email' => $email,
            'country_code' => '+62',
            'phone' => GeneralService::formatPhoneNumber($phone),
            'password' => bcrypt($password ?: 'password'),
            'role' => 'admin',
            'is_active' => true, // Will be activated when project is approved
            'is_pic' => true,
        ]);
    }

    /**
     * Get role options
     */
    public static function getRoles()
    {
        return [
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'affiliator' => 'Affiliator',
        ];
    }

    /**
     * Get active admins for project assignment
     */
    public static function getAvailableAdmins($excludeProjectId = null)
    {
        $query = self::where('role', 'admin')
                     ->where('is_active', true);

        if ($excludeProjectId) {
            $query->whereDoesntHave('adminProjects', function ($q) use ($excludeProjectId) {
                $q->where('project_id', $excludeProjectId);
            });
        }

        return $query->get();
    }

    /**
     * Check if admin user is PIC of specific project
     */
    public function isPicOfProject($projectId)
    {
        if ($this->role !== 'admin') {
            return false;
        }
        
        // Check if user is PIC of the project
        return Project::where('id', $projectId)
                    ->where('pic_user_id', $this->id)
                    ->exists();
    }

    /**
     * Check if user can edit specific project (only PIC admin can edit)
     */
    public function canEditProject($projectId)
    {
        // Superadmin can edit all projects
        if ($this->role === 'superadmin') {
            return true;
        }
        
        // Admin can edit only if they are PIC of the project
        if ($this->role === 'admin') {
            return $this->isPicOfProject($projectId);
        }
        
        return false;
    }

    /**
     * Check if user can view specific project (all assigned admins can view)
     */
    public function canViewProject($projectId)
    {
        // Superadmin can view all projects
        if ($this->role === 'superadmin') {
            return true;
        }
        
        // Admin can view if they are assigned to the project (PIC or regular admin)
        if ($this->role === 'admin') {
            return $this->canManageProject($projectId);
        }
        
        return false;
    }
}