<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\CRM\ProjectCRM;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'require_digital_signature' => 'boolean',
        'is_agreement_accepted' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function affiliatorProjects()
    {
        return $this->hasMany(AffiliatorProject::class);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'project_admins');
    }

    public function projectAdmins()
    {
        return $this->belongsToMany(User::class, 'project_admins')
            ->wherePivot('role', 'admin');
    }

    public function projectCrm()
    {
        return $this->belongsTo(ProjectCRM::class, 'crm_project_id');
    }

    public function leads()
    {
        return $this->hasManyThrough(Lead::class, AffiliatorProject::class);
    }

    public function faqs()
    {
        return $this->hasMany(Faq::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function commissionWithdrawals()
    {
        return $this->hasMany(CommissionWithdrawal::class);
    }

    public function commissionHistories()
    {
        return $this->hasManyThrough(CommissionHistory::class, Unit::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get PIC user - updated relationship
     */
    public function picUser()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    /**
     * Project registrations
     */
    public function registrations()
    {
        return $this->hasMany(ProjectRegistration::class);
    }

    /**
     * Latest registration
     */
    public function latestRegistration()
    {
        return $this->hasOne(ProjectRegistration::class)->latest();
    }

    /**
     * Approved by user
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    //scope term and condition is true

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    /**
     * Scope for internal projects (created by superadmin)
     */
    public function scopeInternalProjects($query)
    {
        return $query->where('registration_type', 'internal');
    }

    /**
     * Scope for manual registration projects
     */
    public function scopeManualRegistration($query)
    {
        return $query->where('registration_type', 'manual');
    }

    /**
     * Scope for CRM projects
     */
    public function scopeCrmProjects($query)
    {
        return $query->where('registration_type', 'crm');
    }

    /**
     * Scope for pending registration
     */
    public function scopePendingRegistration($query)
    {
        return $query->where('registration_status', 'pending');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        // Generate unique slug
        $slug = Str::slug($value);
        $count = 1;
        while (Project::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = Str::slug($value) . '-' . $count;
            $count++;
        }
        $this->attributes['slug'] = $slug;
    }

    // Accessors
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : asset('img/default.jpg');
    }

    /**
     * Get brochure file URL
     */
    public function getBrochureFileUrlAttribute()
    {
        return $this->brochure_file ? asset('storage/' . $this->brochure_file) : null;
    }

    /**
     * Get price list file URL
     */
    public function getPriceListFileUrlAttribute()
    {
        return $this->price_list_file ? asset('storage/' . $this->price_list_file) : null;
    }

    /**
     * Get commission payment trigger label
     */
    public function getCommissionPaymentTriggerLabelAttribute()
    {
        return match($this->commission_payment_trigger) {
            'booking_fee' => 'Booking Fee',
            'akad_kredit' => 'Akad Kredit',
            'spk' => 'SPK (Surat Perjanjian Kerja)',
            default => '-'
        };
    }

    /**
     * Get registration status label
     */
    public function getRegistrationStatusLabelAttribute()
    {
        return match($this->registration_status) {
            'draft' => 'Draft',
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get registration status color
     */
    public function getRegistrationStatusColorAttribute()
    {
        return match($this->registration_status) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get registration type label
     */
    public function getRegistrationTypeLabelAttribute()
    {
        return match($this->registration_type) {
            'internal' => 'Internal',
            'crm' => 'CRM',
            'manual' => 'Manual',
            default => 'Unknown'
        };
    }

    /**
     * Get project period formatted
     */
    public function getProjectPeriodAttribute()
    {
        if (!$this->start_date) {
            return '-';
        }

        $start = $this->start_date->format('d/m/Y');
        
        if ($this->end_date) {
            $end = $this->end_date->format('d/m/Y');
            return "{$start} - {$end}";
        }

        return "{$start} - Tidak terbatas";
    }

    /**
     * Check if project is manually registered
     */
    public function getIsManualRegistrationAttribute()
    {
        return $this->registration_type === 'manual';
    }

    /**
     * Check if project is internal (created by superadmin)
     */
    public function getIsInternalProjectAttribute()
    {
        return $this->registration_type === 'internal';
    }

    /**
     * Check if project is from CRM
     */
    public function getIsCrmProjectAttribute()
    {
        return $this->registration_type === 'crm';
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // remove br in terms and conditions
    public function getTermsAndConditionsAttribute($value)
    {
        return str_replace(["\r\n", "\n", "\r"], '', $value);
    }

    /**
     * Get commission information for this project
     */
    public function getCommissionInfoAttribute()
    {
        $units = $this->units()->active()->get();
        
        if ($units->isEmpty()) {
            return [
                'range' => 'Komisi Tidak Tersedia',
                'description' => 'Belum ada unit yang tersedia',
                'type' => 'none'
            ];
        }

        $percentageUnits = $units->where('commission_type', 'percentage');
        $fixedUnits = $units->where('commission_type', 'fixed');

        $ranges = [];
        $descriptions = [];

        if ($percentageUnits->count() > 0) {
            $minPercentage = $percentageUnits->min('commission_value');
            $maxPercentage = $percentageUnits->max('commission_value');
            
            if ($minPercentage == $maxPercentage) {
                $ranges[] = number_format($minPercentage, 1) . '%';
            } else {
                $ranges[] = number_format($minPercentage, 1) . '% - ' . number_format($maxPercentage, 1) . '%';
            }
            
            $descriptions[] = 'komisi persentase dari harga unit';
        }

        if ($fixedUnits->count() > 0) {
            $minFixed = $fixedUnits->min('commission_value');
            $maxFixed = $fixedUnits->max('commission_value');
            
            if ($minFixed == $maxFixed) {
                $ranges[] = 'Rp ' . number_format($minFixed, 0, ',', '.');
            } else {
                $ranges[] = 'Rp ' . number_format($minFixed, 0, ',', '.') . ' - Rp ' . number_format($maxFixed, 0, ',', '.');
            }
            
            $descriptions[] = 'komisi tetap per unit';
        }

        return [
            'range' => implode(' atau ', $ranges),
            'description' => 'Anda akan mendapat ' . implode(' atau ', $descriptions) . ' tergantung unit yang dipilih customer',
            'type' => $percentageUnits->count() > 0 && $fixedUnits->count() > 0 ? 'mixed' : 
                     ($percentageUnits->count() > 0 ? 'percentage' : 'fixed')
        ];
    }

    /**
     * Check if user can join this project
     */
    public function canBeJoinedBy(User $user)
    {
        // Check if project is active
        if (!$this->is_active) {
            return [
                'can_join' => false,
                'reason' => 'Project ini sedang tidak aktif'
            ];
        }

        // Check if user already joined
        $alreadyJoined = $user->affiliatorProjects()
            ->where('project_id', $this->id)
            ->exists();
        
        if ($alreadyJoined) {
            return [
                'can_join' => false,
                'reason' => 'Anda sudah bergabung dengan project ini'
            ];
        }

        // Check max projects limit for user
        $maxProjects = \App\Models\SystemSetting::getValue('max_projects_per_affiliator', 3);
        $currentCount = $user->affiliatorProjects()->count();
        
        if ($currentCount >= $maxProjects) {
            return [
                'can_join' => false,
                'reason' => "Anda sudah mencapai batas maksimal {$maxProjects} project"
            ];
        }

        // Check max affiliators per project if setting exists
        $maxAffiliators = \App\Models\SystemSetting::getValue('max_affiliators_per_project', null);
        if ($maxAffiliators) {
            $currentAffiliators = $this->affiliatorProjects()->count();
            
            if ($currentAffiliators >= $maxAffiliators) {
                return [
                    'can_join' => false,
                    'reason' => 'Project sudah mencapai batas maksimal affiliator'
                ];
            }
        }

        // Check if user is active
        if (!$user->is_active) {
            return [
                'can_join' => false,
                'reason' => 'Akun Anda tidak aktif'
            ];
        }

        return [
            'can_join' => true,
            'reason' => null
        ];
    }

    /**
     * Get total affiliators count
     */
    public function getTotalAffiliatorsAttribute()
    {
        return $this->affiliatorProjects()->count();
    }

    /**
     * Get active affiliators count
     */
    public function getActiveAffiliatorsAttribute()
    {
        return $this->affiliatorProjects()->where('status', 'active')->count();
    }

    /**
     * Get pending affiliators count
     */
    public function getPendingAffiliatorsAttribute()
    {
        return $this->affiliatorProjects()->where('verification_status', 'pending')->count();
    }

    /**
     * Get total leads count
     */
    public function getTotalLeadsAttribute()
    {
        return $this->leads()->count();
    }

    /**
     * Get verified leads count
     */
    public function getVerifiedLeadsAttribute()
    {
        return $this->leads()->verified()->count();
    }

    /**
     * Get total commission paid
     */
    public function getTotalCommissionPaidAttribute()
    {
        return $this->commissionHistories()->where('type', 'earned')->sum('amount');
    }

    /**
     * Get project statistics
     */
    public function getStatistics()
    {
        return [
            'total_affiliators' => $this->total_affiliators,
            'active_affiliators' => $this->active_affiliators,
            'pending_affiliators' => $this->pending_affiliators,
            'total_leads' => $this->total_leads,
            'verified_leads' => $this->verified_leads,
            'total_commission_paid' => $this->total_commission_paid,
            'total_units' => $this->units()->count(),
            'active_units' => $this->units()->active()->count(),
            'commission_info' => $this->commission_info
        ];
    }

    /**
     * Static method to get commission payment trigger options
     */
    public static function getCommissionPaymentTriggers()
    {
        return [
            'booking_fee' => 'Booking Fee',
            'akad_kredit' => 'Akad Kredit', 
            'spk' => 'SPK (Surat Perjanjian Kerja)',
        ];
    }

    /**
     * Static method to get registration status options
     */
    public static function getRegistrationStatuses()
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
    }

    /**
     * Static method to get registration types
     */
    public static function getRegistrationTypes()
    {
        return [
            'internal' => 'Internal (SuperAdmin)',
            'crm' => 'CRM System',
            'manual' => 'Manual Registration',
        ];
    }
}