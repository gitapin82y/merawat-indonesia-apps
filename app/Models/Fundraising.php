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

// Relasi ke donations berdasarkan referral_code (untuk backward compatibility)
    public function donationsRelation()
    {
        return $this->hasMany(Donation::class, 'referral_code', 'code_link');
    }

    // Method untuk mendapatkan donations berdasarkan array ID di kolom donations
    public function getDonationsFromArray()
    {
        if (empty($this->donations) || !is_array($this->donations)) {
            return collect();
        }
        
        return Donation::whereIn('id', $this->donations)->get();
    }

    // Method untuk filter berdasarkan tanggal donations dari array
    public function scopeWithDonationsInPeriod($query, $startDate, $endDate = null)
    {
        return $query->whereHas('donationsFromArray', function($q) use ($startDate, $endDate) {
            $q->whereDate('created_at', '>=', $startDate);
            if ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            }
            $q->where('status', 'success');
        });
    }

    // Method untuk mendapatkan donations yang sudah success berdasarkan array ID
    public function getSuccessfulDonationsFromArray()
    {
        if (empty($this->donations) || !is_array($this->donations)) {
            return collect();
        }
        
        return Donation::whereIn('id', $this->donations)
                      ->where('status', 'success')
                      ->get();
    }

    // Method untuk mendapatkan donations dalam periode tertentu dari array
    public function getDonationsInPeriod($startDate, $endDate = null)
    {
        if (empty($this->donations) || !is_array($this->donations)) {
            return collect();
        }
        
        $query = Donation::whereIn('id', $this->donations)
                         ->where('status', 'success')
                         ->whereDate('created_at', '>=', $startDate);
        
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        return $query->get();
    }

    // Method untuk menghitung commission berdasarkan donations tertentu
    public function calculateCommissionFromDonations($donations, $commissionRate = null)
    {
        if ($commissionRate === null) {
            $commissionRate = \App\Models\Commission::first()->amount ?? 5;
        }
        
        $totalAmount = $donations->sum('amount');
        return ($totalAmount * $commissionRate) / 100;
    }
}
