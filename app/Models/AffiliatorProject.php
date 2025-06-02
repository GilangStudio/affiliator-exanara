<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliatorProject extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'verified_at' => 'datetime',
        'terms_accepted' => 'boolean',
        'terms_accepted_at' => 'datetime',
        'digital_signature_at' => 'datetime',
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

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

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
        return $query->where($this->getTable().'ve.rification_status', 'rejected');
    }

    // Accessors
    public function getKtpPhotoUrlAttribute()
    {
        return $this->ktp_photo ? asset('storage/' . $this->ktp_photo) : null;
    }

    public function getIsVerifiedAttribute()
    {
        return $this->verification_status === 'verified';
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getCanAddLeadsAttribute()
    {
        return $this->status === 'active' && 
               $this->verification_status === 'verified' && 
               $this->terms_accepted && 
               $this->digital_signature;
    }

    public function getCompletionProgressAttribute()
    {
        $steps = [
            'ktp_uploaded' => !empty($this->ktp_number) && !empty($this->ktp_photo),
            'verified' => $this->verification_status === 'verified',
            'terms_accepted' => $this->terms_accepted,
            'digital_signature' => !empty($this->digital_signature)
        ];

        $completed = array_filter($steps);
        return (count($completed) / count($steps)) * 100;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'incomplete' => 'Belum Lengkap',
            'pending_verification' => 'Menunggu Verifikasi',
            'active' => 'Aktif',
            'suspended' => 'Disuspend',
            default => 'Tidak Diketahui'
        };
    }
}
