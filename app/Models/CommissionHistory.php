<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommissionHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    public function scopeWithdrawn($query)
    {
        return $query->where('type', 'withdrawn');
    }

    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    // Accessors
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'earned' => 'Komisi Diperoleh',
            'withdrawn' => 'Komisi Ditarik',
            'adjustment' => 'Penyesuaian',
            default => 'Lainnya'
        };
    }

    public function getAmountFormattedAttribute()
    {
        $prefix = $this->type === 'withdrawn' ? '-' : '+';
        return $prefix . 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'earned' => 'success',
            'withdrawn' => 'danger',
            'adjustment' => 'blue',
            default => 'gray'
        };
    }
}
