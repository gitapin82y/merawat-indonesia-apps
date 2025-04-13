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
}
