<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KabarPencairan extends Model
{
    use HasFactory;

    protected $table = 'kabar_pencairan';

    protected $fillable = [
        'campaign_id', 'title', 'description', 'total_amount', 'document_rab','status'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
