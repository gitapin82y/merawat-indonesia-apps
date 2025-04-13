<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrioritasCampaign extends Model
{
    use HasFactory;

    protected $table = 'prioritas_campaigns';

    protected $fillable = [
        'campaign_id', 'prioritas'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
