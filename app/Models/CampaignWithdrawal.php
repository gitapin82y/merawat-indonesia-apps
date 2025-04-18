<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'admin_id', 'amount', 'status', 
        'document_rab', 'bukti_pencairan', 'account_number','account_name', 'payment_method','rejection_reason'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
