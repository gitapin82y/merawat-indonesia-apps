<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

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

// Di model User.php
public function getAvatarUrlAttribute()
{
    return $this->avatar && !Str::startsWith($this->avatar, 'default/') 
        ? asset('storage/' . $this->avatar) 
        : asset('assets/img/' . $this->avatar);
}

public function getThumbnailUrlAttribute()
{
    return $this->thumbnail && !Str::startsWith($this->thumbnail, 'default/') 
        ? asset('storage/' . $this->thumbnail) 
        : asset('assets/img/' . $this->thumbnail);
}

}
