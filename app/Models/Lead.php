<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'deal_value' => 'decimal:2',
        'commission_earned' => 'decimal:2',
        'commission_paid' => 'boolean',
        'verified_at' => 'datetime',
        'commission_paid_at' => 'datetime',
        'sent_to_crm_at' => 'datetime',
    ];

    // Relationships
    public function affiliatorProject()
    {
        return $this->belongsTo(AffiliatorProject::class);
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, AffiliatorProject::class, 'id', 'id', 'affiliator_project_id', 'user_id');
    }

    public function project()
    {
        return $this->hasOneThrough(Project::class, AffiliatorProject::class, 'id', 'id', 'affiliator_project_id', 'project_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function statusHistories()
    {
        return $this->hasMany(LeadStatusHistory::class);
    }

    public function commissionHistories()
    {
        return $this->hasMany(CommissionHistory::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where($this->getTable() . '.verification_status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where($this->getTable() . '.verification_status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where($this->getTable() . '.verification_status', 'rejected');
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->whereHas('affiliatorProject', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });
    }

    // Accessors
    public function getIsVerifiedAttribute()
    {
        return $this->verification_status === 'verified';
    }

    public function getIsPendingAttribute()
    {
        return $this->verification_status === 'pending';
    }

    public function getIsRejectedAttribute()
    {
        return $this->verification_status === 'rejected';
    }

    public function getVerificationStatusLabelAttribute()
    {
        return match($this->verification_status) {
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default => 'Tidak Diketahui'
        };
    }

    public function getCommissionFormattedAttribute()
    {
        return 'Rp ' . number_format($this->commission_earned, 0, ',', '.');
    }

    public function getDealValueFormattedAttribute()
    {
        return $this->deal_value ? 'Rp ' . number_format($this->deal_value, 0, ',', '.') : '-';
    }

    public function getProjectLocationAttribute()
    {
        return $this->project ? $this->project->location : null;
    }

    // Methods
    public function calculateCommission()
    {
        if (!$this->unit) {
            return 0;
        }
        
        return $this->unit->calculateCommission($this->deal_value);
    }

    public function updateCommission()
    {
        $this->commission_earned = $this->calculateCommission();
        $this->save();
        
        return $this->commission_earned;
    }
}
