<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:0',
        // 'commission_value' => 'decimal:0',
        'is_active' => 'boolean',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'carport' => 'integer',
        'floor' => 'integer',
        'crm_unit_id' => 'integer',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function commissionHistories()
    {
        return $this->hasMany(CommissionHistory::class);
    }

    public function commissionWithdrawals()
    {
        return $this->hasMany(CommissionWithdrawal::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByType($query, $unitType)
    {
        return $query->where('unit_type', $unitType);
    }

    public function scopeByCrmId($query, $crmUnitId)
    {
        return $query->where('crm_unit_id', $crmUnitId);
    }

    public function scopeWithBedrooms($query, $bedrooms)
    {
        return $query->where('bedrooms', $bedrooms);
    }

    public function scopePriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    public function scopeOnFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope for unit status
     */
    public function scopeReady($query)
    {
        return $query->where('unit_status', 'ready');
    }

    public function scopeIndent($query)
    {
        return $query->where('unit_status', 'indent');
    }

    public function scopeSoldOut($query)
    {
        return $query->where('unit_status', 'sold_out');
    }

    /**
     * Scope for available units (ready + indent)
     */
    public function scopeAvailable($query)
    {
        return $query->whereIn('unit_status', ['ready', 'indent']);
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('img/default.jpg');
    }

    public function getPriceFormattedAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getCommissionDisplayAttribute()
    {
        if ($this->commission_type === 'percentage') {
            return number_format($this->commission_value, 2) . '%';
        }
        return 'Rp ' . number_format($this->commission_value, 0, ',', '.');
    }

    public function getUnitTypeDisplayAttribute()
    {
        $types = [
            'residential' => 'Residential',
            'commercial' => 'Komersial',
            'office' => 'Perkantoran',
            'retail' => 'Retail',
            'warehouse' => 'Gudang',
            'industrial' => 'Industri',
            'mixed_use' => 'Mixed Use',
            'other' => 'Lainnya'
        ];

        return $types[$this->unit_type] ?? ucfirst($this->unit_type ?? 'Tidak Diketahui');
    }

    public function getBuildingAreaFormattedAttribute()
    {
        return $this->building_area ? $this->building_area . ' m²' : 'Tidak tersedia';
    }

    public function getLandAreaFormattedAttribute()
    {
        return $this->land_area ? $this->land_area . ' m²' : 'Tidak tersedia';
    }

    public function getUnitSpecsAttribute()
    {
        $specs = [];
        
        if ($this->building_area) {
            $specs[] = 'LB: ' . $this->building_area . ' m²';
        }
        
        if ($this->land_area) {
            $specs[] = 'LT: ' . $this->land_area . ' m²';
        }
        
        if ($this->bedrooms) {
            $specs[] = $this->bedrooms . ' KT';
        }
        
        if ($this->bathrooms) {
            $specs[] = $this->bathrooms . ' KM';
        }
        
        if ($this->carport) {
            $specs[] = $this->carport . ' Carport';
        }
        
        if ($this->floor) {
            $specs[] = 'Lt. ' . $this->floor;
        }

        if ($this->power_capacity) {
            $specs[] = $this->power_capacity . ' VA';
        }
    
        if ($this->certificate_type) {
            $specs[] = $this->certificate_type;
        }
        
        return implode(' • ', $specs);
    }

    // public function getFullNameAttribute()
    // {
    //     return $this->name . ' (' . $this->code . ')';
    // }

    public function getProjectLocationAttribute()
    {
        return $this->project ? $this->project->location : null;
    }

    public function getIsSyncedToCrmAttribute()
    {
        return !is_null($this->crm_unit_id);
    }

    // Methods
    public function calculateCommission($saleAmount = null)
    {
        $baseAmount = $saleAmount ?? $this->price;
        
        if ($this->commission_type === 'percentage') {
            return ($baseAmount * $this->commission_value) / 100;
        }
        
        return $this->commission_value;
    }

    public function getCommissionAmountAttribute()
    {
        return $this->calculateCommission();
    }

    public function getCommissionAmountFormattedAttribute()
    {
        return 'Rp ' . number_format($this->calculateCommission(), 0, ',', '.');
    }

    public function syncToCrm($crmUnitId)
    {
        $this->update(['crm_unit_id' => $crmUnitId]);
        return $this;
    }

    public function unsyncFromCrm()
    {
        $this->update(['crm_unit_id' => null]);
        return $this;
    }

    public function getTotalLeadsCount()
    {
        return $this->leads()->count();
    }

    public function getVerifiedLeadsCount()
    {
        return $this->leads()->verified()->count();
    }

    public function getTotalCommissionEarned()
    {
        return $this->commissionHistories()->where('type', 'earned')->sum('amount');
    }

    /**
     * Check if unit is available for sale
     */
    public function getIsAvailableAttribute()
    {
        return in_array($this->unit_status, ['ready', 'indent']) && $this->is_active;
    }

    /**
     * Get availability status with color
     */
    public function getAvailabilityStatusAttribute()
    {
        if (!$this->is_active) {
            return [
                'label' => 'Tidak Aktif',
                'color' => 'secondary'
            ];
        }

        return [
            'label' => $this->unit_status_label,
            'color' => $this->unit_status_color
        ];
    }

    /**
     * Get unit status label
     */
    public function getUnitStatusLabelAttribute()
    {
        return match($this->unit_status) {
            'ready' => 'Ready',
            'indent' => 'Indent',
            'sold_out' => 'Sold Out',
            default => 'Unknown'
        };
    }

    /**
     * Get unit status color
     */
    public function getUnitStatusColorAttribute()
    {
        return match($this->unit_status) {
            'ready' => 'success',
            'indent' => 'warning',
            'sold_out' => 'danger',
            default => 'secondary'
        };
    }

    // public static function generateUniqueCode($projectId, $prefix = null)
    // {
    //     $project = Project::find($projectId);
        
    //     if (!$project) {
    //         throw new \Exception('Project tidak ditemukan');
    //     }
        
    //     // Generate prefix dari nama project atau gunakan prefix yang diberikan
    //     $baseCode = $prefix ?? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $project->name), 0, 3));
        
    //     // Jika prefix kosong, gunakan default
    //     if (empty($baseCode)) {
    //         $baseCode = 'UNT';
    //     }
        
    //     $counter = 1;
    //     do {
    //         $code = $baseCode . str_pad($counter, 4, '0', STR_PAD_LEFT);
    //         $counter++;
    //     } while (self::where('code', $code)->exists());
        
    //     return $code;
    // }

    public static function getUnitTypes()
    {
        return [
            'residential' => 'Residential',
            'commercial' => 'Komersial',
            'office' => 'Perkantoran',
            'retail' => 'Retail',
            'warehouse' => 'Gudang',
            'industrial' => 'Industri',
            'mixed_use' => 'Mixed Use',
            'other' => 'Lainnya'
        ];
    }

    public static function getCommissionTypes()
    {
        return [
            'percentage' => 'Persentase (%)',
            'fixed' => 'Nominal Tetap (Rp)'
        ];
    }

    /**
     * Static method to get unit status options
     */
    public static function getUnitStatuses()
    {
        return [
            'ready' => 'Ready',
            'indent' => 'Indent',
            'sold_out' => 'Sold Out',
        ];
    }

    /**
     * Static method to get certificate type options
     */
    public static function getCertificateTypes()
    {
        return [
            'SHM' => 'Sertifikat Hak Milik (SHM)',
            'HGB' => 'Hak Guna Bangunan (HGB)', 
            'AJB' => 'Akta Jual Beli (AJB)',
        ];
    }

    // Boot method untuk auto generate code
    // protected static function boot()
    // {
    //     parent::boot();
        
    //     static::creating(function ($unit) {
    //         if (empty($unit->code)) {
    //             $unit->code = self::generateUniqueCode($unit->project_id);
    //         }
    //     });
    // }

    // Mutators
    public function setUnitTypeAttribute($value)
    {
        $this->attributes['unit_type'] = strtolower($value);
    }
}