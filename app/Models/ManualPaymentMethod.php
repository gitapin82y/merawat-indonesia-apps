<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManualPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'account_number', 'account_name',
        'is_active', 'icon', 'instructions'
    ];

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }
}