<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectRegistration extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'form_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu Review',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Tidak Diketahui'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getCommissionPaymentTriggerLabelAttribute()
    {
        if (!$this->form_data || !isset($this->form_data['commission_payment_trigger'])) {
            return '-';
        }

        return match($this->form_data['commission_payment_trigger']) {
            'booking_fee' => 'Booking Fee',
            'akad_kredit' => 'Akad Kredit',
            'spk' => 'SPK (Surat Perjanjian Kerja)',
            default => '-'
        };
    }

    public function getUnitsCountAttribute()
    {
        if (!$this->form_data || !isset($this->form_data['units'])) {
            return 0;
        }

        return count($this->form_data['units']);
    }

    public function getProjectPeriodAttribute()
    {
        if (!$this->form_data) {
            return '-';
        }

        $startDate = $this->form_data['start_date'] ?? null;
        $endDate = $this->form_data['end_date'] ?? null;

        if (!$startDate) {
            return '-';
        }

        $start = \Carbon\Carbon::parse($startDate)->format('d/m/Y');
        
        if ($endDate) {
            $end = \Carbon\Carbon::parse($endDate)->format('d/m/Y');
            return "{$start} - {$end}";
        }

        return "{$start} - Tidak terbatas";
    }

    public function getPicInfoAttribute()
    {
        if (!$this->form_data) {
            return null;
        }

        return [
            'name' => $this->form_data['pic_name'] ?? '-',
            'phone' => $this->form_data['pic_phone'] ?? '-',
            'email' => $this->form_data['pic_email'] ?? '-',
        ];
    }

    // Methods
    public function approve($adminId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'review_notes' => $notes
        ]);

        // Activate the project and units
        $this->project->update([
            'registration_status' => 'approved',
            'is_active' => true,
            'approved_by' => $adminId,
            'approved_at' => now()
        ]);

        // Activate all units
        $this->project->units()->update(['is_active' => true]);

        // Activate PIC user
        if ($this->project->pic_email) {
            $picUser = User::where('email', $this->project->pic_email)->first();
            if ($picUser) {
                $picUser->update(['is_active' => true]);
            }
        }

        return $this;
    }

    public function reject($adminId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'review_notes' => $reason
        ]);

        // Update project status
        $this->project->update([
            'registration_status' => 'rejected',
            'rejection_reason' => $reason
        ]);

        return $this;
    }

    public static function getStatistics()
    {
        return [
            'total' => self::count(),
            'pending' => self::pending()->count(),
            'approved' => self::approved()->count(),
            'rejected' => self::rejected()->count(),
            'this_month' => self::whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                              ->count(),
        ];
    }
}