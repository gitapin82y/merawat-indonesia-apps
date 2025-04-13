<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'phone', 'leader_name', 'address', 
        'legality', 'email', 'avatar', 'thumbnail', 
        'social', 'status', 'log_activity','bio'
    ];

    protected $casts = [
        'social' => 'array',
        'log_activity' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function campaignWithdrawals()
    {
        return $this->hasMany(CampaignWithdrawal::class);
    }
}
