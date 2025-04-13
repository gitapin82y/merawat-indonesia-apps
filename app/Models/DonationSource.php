<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DonationSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type', 'campaign_name', 'utm_source', 
        'utm_medium', 'utm_campaign', 'total_donations', 'total_amount'
    ];

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }
}
