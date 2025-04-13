<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'user_id', 'name', 'doa', 'is_anonymous', 
        'phone', 'email', 'snap_token', 'amount', 'payment_type', 
        'payment_method', 'status'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function donationLikes()
    {
        return $this->hasMany(DonationLike::class);
    }

}
