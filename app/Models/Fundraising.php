<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fundraising extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'user_id', 'code_link', 'total_donatur', 
        'donations', 'jumlah_donasi', 'commission'
    ];

    protected $casts = [
        'donations' => 'array'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fundraisingWithdrawals()
    {
        return $this->hasMany(FundraisingWithdrawal::class);
    }

    public function donations()
{
    return $this->hasMany(Donation::class, 'referral_code', 'code_link');
}

// Method untuk filter berdasarkan donations
public function scopeFilterByDonationDate($query, $startDate, $endDate = null)
{
    return $query->whereHas('donations', function($q) use ($startDate, $endDate) {
        $q->whereDate('created_at', '>=', $startDate);
        if ($endDate) {
            $q->whereDate('created_at', '<=', $endDate);
        }
    });
}
}
