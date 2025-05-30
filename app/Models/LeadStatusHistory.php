<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadStatusHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scopes
    public function scopeVerificationHistory($query)
    {
        return $query->where('status_type', 'verification');
    }

    public function scopeCrmHistory($query)
    {
        return $query->where('status_type', 'crm');
    }
}
