<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DonationLike extends Model
{
    use HasFactory;

    protected $table = 'donation_likes';

    protected $fillable = [
        'donation_id', 'user_id','guest_identifier'
    ];

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
