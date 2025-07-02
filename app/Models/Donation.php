<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'user_id', 'name', 'doa', 'is_anonymous',  'is_contactable',
        'phone', 'email', 'snap_token', 'amount', 'payment_type', 
        'payment_method', 'status', 'manual_payment_method_id', 'payment_proof', 'donation_source_id','utm_source','utm_medium','utm_campaign','unique_code','referral_code'
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

    public function manualPaymentMethod()
    {
        return $this->belongsTo(ManualPaymentMethod::class);
    }

}
