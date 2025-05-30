<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankAccount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commissionWithdrawals()
    {
        return $this->hasMany(CommissionWithdrawal::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Accessors
    public function getMaskedAccountNumberAttribute()
    {
        $accountNumber = $this->account_number;
        $length = strlen($accountNumber);
        
        if ($length <= 4) {
            return $accountNumber;
        }
        
        return str_repeat('*', $length - 4) . substr($accountNumber, -4);
    }

    public function getFullBankNameAttribute()
    {
        return strtoupper($this->bank_name);
    }

    public function getVerificationStatusLabelAttribute()
    {
        return $this->is_verified ? 'Terverifikasi' : 'Belum Terverifikasi';
    }

    public static function getBankOptions()
    {
        return [
            'BCA' => 'Bank Central Asia (BCA)',
            'MANDIRI' => 'Bank Mandiri',
            'BNI' => 'Bank Negara Indonesia (BNI)',
            'BRI' => 'Bank Rakyat Indonesia (BRI)',
            'CIMB' => 'CIMB Niaga',
            'DANAMON' => 'Bank Danamon',
            'PERMATA' => 'Bank Permata',
            'BTPN' => 'Bank BTPN',
            'MEGA' => 'Bank Mega',
            'OCBC' => 'OCBC NISP',
            'PANIN' => 'Panin Bank',
            'MAYBANK' => 'Maybank Indonesia',
            'BSI' => 'Bank Syariah Indonesia (BSI)',
            'MUAMALAT' => 'Bank Muamalat',
            'JENIUS' => 'Jenius (BTPN)',
            'GOPAY' => 'GoPay',
            'OVO' => 'OVO',
            'DANA' => 'DANA',
            'LINKAJA' => 'LinkAja',
            'SHOPPEEPAY' => 'ShoppePay',
        ];
    }
}
