<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FundraisingWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'fundraising_id', 'user_id', 'amount', 'status', 
        'bukti_pencairan', 'account_number','account_name', 'payment_method'
    ];

    public function fundraising()
    {
        return $this->belongsTo(Fundraising::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
