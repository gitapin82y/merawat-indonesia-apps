<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KabarTerbaru extends Model
{
    use HasFactory;

    protected $table = 'kabar_terbaru';

    protected $fillable = [
        'campaign_id', 'title', 'description'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
