<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'properties' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Accessors
    public function getActionLabelAttribute()
    {
        $labels = [
            'login' => 'Masuk Sistem',
            'logout' => 'Keluar Sistem',
            'register' => 'Registrasi',
            'add_lead' => 'Tambah Lead',
            'verify_lead' => 'Verifikasi Lead',
            'reject_lead' => 'Tolak Lead',
            'request_withdrawal' => 'Request Penarikan',
            'approve_withdrawal' => 'Setujui Penarikan',
            'reject_withdrawal' => 'Tolak Penarikan',
            'process_withdrawal' => 'Proses Penarikan',
            'upload_ktp' => 'Upload KTP',
            'accept_terms' => 'Setujui S&K',
            'digital_signature' => 'Tanda Tangan Digital',
            'update_profile_photo' => 'Update Foto Profil',
            'commission_earned' => 'Komisi Diperoleh',
            'create_unit' => 'Buat Unit',
            'update_unit' => 'Update Unit',
            'delete_unit' => 'Hapus Unit',
            'unit_sold' => 'Unit Terjual',
            'unit_reserved' => 'Unit Dipesan',
            'unit_available' => 'Unit Tersedia',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    public function getBrowserAttribute()
    {
        if (!$this->user_agent) return 'Unknown';
        
        $userAgent = $this->user_agent;
        
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        
        return 'Unknown';
    }
}