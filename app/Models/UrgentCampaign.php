<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrgentCampaign extends Model
{
    use HasFactory;

      protected $table = 'urgent_campaigns';

    protected $fillable = [
        'campaign_id',
        'prioritas'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}