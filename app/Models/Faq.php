<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faq extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('project_id');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getIsGlobalAttribute()
    {
        return is_null($this->project_id);
    }

    public function getCategoryBadgeColorAttribute()
    {
        $colors = [
            'general' => 'blue',
            'registration' => 'green',
            'commission' => 'purple',
            'withdrawal' => 'yellow',
            'leads' => 'orange',
            'other' => 'gray'
        ];

        return $colors[$this->category] ?? 'gray';
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            'general' => 'Umum',
            'project' => 'Project',
            'payment' => 'Pembayaran',
            'technical' => 'Teknis',
            'commission' => 'Komisi',
            'account' => 'Akun',
            'other' => 'Lainnya'
        ];

        return $labels[$this->category] ?? 'Lainnya';
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

    // Untuk kompatibilitas dengan kode yang sudah ada
    public function getIsFeaturedAttribute()
    {
        return $this->is_featured ?? false;
    }

    // Methods
    public static function getCategories()
    {
        return [
            'general' => 'Umum',
            'registration' => 'Registrasi', 
            'commission' => 'Komisi',
            'withdrawal' => 'Penarikan',
            'leads' => 'Leads',
            'other' => 'Lainnya'
        ];
    }

    public static function getNextSortOrder($projectId = null)
    {
        return self::when($projectId, function ($query) use ($projectId) {
                    $query->where('project_id', $projectId);
                }, function ($query) {
                    $query->whereNull('project_id');
                })
                ->max('sort_order') + 1;
    }
}