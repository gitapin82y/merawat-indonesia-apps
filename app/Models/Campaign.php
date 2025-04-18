<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id', 'category_id', 'photo', 'title','slug', 'description', 
        'status', 'deadline', 'total_donatur', 'total_kabar_terbaru', 
        'total_pencairan_dana', 'jumlah_pencarian', 'current_donation', 
        'jumlah_donasi', 'jumlah_target_donasi', 'document_rab', 
        'bukti_pencairan_dana'
    ];

    protected $casts = [
        'deadline' => 'date'
    ];

    public function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;
        
        // Pastikan slug unik
        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        
        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_campaign_save');
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function donations()//donation dengan data doa not null merupakan doa orang baik
    {
        return $this->hasMany(Donation::class);
    }

    public function fundraisings()
    {
        return $this->hasMany(Fundraising::class);
    }

    public function kabarTerbaru()
    {
        return $this->hasMany(KabarTerbaru::class);
    }

    public function kabarPencairan()
    {
        return $this->hasMany(KabarPencairan::class);
    }

    public function campaignWithdrawals()//anda dapat mengambil rp total pencairan dana
    {
        return $this->hasMany(CampaignWithdrawal::class);
    }

    public function prioritasCampaign()
    {
        return $this->hasOne(PrioritasCampaign::class);
    }
    
    public function getRemainingDaysAttribute()
    {
        return $this->deadline ? round(now()->startOfDay()->diffInDays($this->deadline->startOfDay())) : 0;
    }

    // Accessor for progress percentage
    public function getProgressPercentageAttribute()
    {
        return $this->jumlah_target_donasi ? 
            round(($this->jumlah_donasi / $this->jumlah_target_donasi) * 100) : 
            0;
    }
}
