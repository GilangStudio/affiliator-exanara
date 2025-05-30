<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'is_active' => 'boolean',
        'require_digital_signature' => 'boolean',
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
        return $this->hasMany(CommissionHistory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        // $this->attributes['slug'] = Str::slug($value);
        //slug must be unique
        $slug = Str::slug($value);
        $count = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = Str::slug($value) . '-' . $count;
            $count++;
        }
        $this->attributes['slug'] = $slug;
    }

    // Accessors
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getCommissionDisplayAttribute()
    {
        if ($this->commission_type === 'percentage') {
            return $this->commission_value . '%';
        }
        return 'Rp ' . number_format($this->commission_value, 0, ',', '.');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
