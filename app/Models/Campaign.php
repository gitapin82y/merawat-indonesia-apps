<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;


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
    
     // Tambahkan appends untuk accessor
     protected $appends = ['progressPercentage', 'remainingDays', 'remainingTime', 'timeFormatted'];




    // Accessor for progress percentage
    public function getProgressPercentageAttribute()
    {
        return $this->jumlah_target_donasi ? 
            round(($this->jumlah_donasi / $this->jumlah_target_donasi) * 100) : 
            0;
    }

     // Accessor untuk menghitung sisa hari
     public function getRemainingDaysAttribute()
     {
         if ($this->deadline) {
             // Pastikan deadline di akhir hari (23:59:59)
             $deadline = Carbon::parse($this->deadline)->endOfDay();
             $now = Carbon::now();
             
             // Jika deadline sudah lewat
             if ($now->gt($deadline)) {
                 return -1;
             }
             
             // Hitung selisih hari (tidak termasuk hari ini jika di hari yang sama)
             if ($now->isSameDay($deadline)) {
                 return 0; // Hari ini adalah deadline, tampilkan dalam jam
             }
             
             // Gunakan diffInDays dan pastikan selalu integer
             return (int) $now->diffInDays($deadline, false);
         }
         return null;
     }
 
     // Accessor untuk mendapatkan sisa waktu dalam jam ketika hari = 0
     public function getRemainingTimeAttribute()
     {
         if ($this->deadline) {
             // Pastikan deadline di akhir hari
             $deadline = Carbon::parse($this->deadline)->endOfDay();
             $now = Carbon::now();
             
             // Jika deadline sudah lewat
             if ($now->gt($deadline)) {
                 return 0;
             }
             
             // Jika hari ini adalah deadline, hitung jam tersisa
             if ($now->isSameDay($deadline) || $this->getRemainingDaysAttribute() == 0) {
                 // Hitung selisih jam dan bulatkan ke bawah
                 return (int) $now->diffInHours($deadline, false);
             }
             
             return 0;
         }
         return null;
     }
 
     // Accessor untuk format waktu yang tersisa (hari atau jam)
     public function getTimeFormattedAttribute()
     {
         if ($this->deadline) {
             $deadline = Carbon::parse($this->deadline)->endOfDay();
             $now = Carbon::now();
             
             if ($now->gt($deadline)) {
                 return '0 jam'; // Sudah lewat deadline
             }
             
             // Jika hari ini adalah deadline, tampilkan jam
             if ($now->isSameDay($deadline) || $this->getRemainingDaysAttribute() == 0) {
                 $diffInHours = $this->getRemainingTimeAttribute();
                 return $diffInHours . ' jam lagi';
             }
             
             return $this->getRemainingDaysAttribute() . ' hari lagi';
         }
         return null;
     }
 
     // Method untuk mengecek dan mengupdate status kampanye berdasarkan deadline
     public static function checkAndUpdateExpiredCampaigns()
     {
         $now = Carbon::now();
         
         // Cari kampanye aktif yang sudah melewati deadline
         $campaigns = Campaign::where('status', 'aktif')
             ->whereNotNull('deadline')
             ->get();
             
         $updatedCount = 0;
         
         foreach ($campaigns as $campaign) {
             // Deadline adalah akhir hari dari tanggal deadline
             $deadline = Carbon::parse($campaign->deadline)->endOfDay();
             
             // Jika waktu sekarang sudah melewati deadline
             if ($now->gt($deadline)) {
                 $campaign->status = 'selesai';
                 $campaign->save();
                 $updatedCount++;
             }
         }
         
         return $updatedCount; // Mengembalikan jumlah kampanye yang diupdate
     }
     
     // Method debugging untuk memeriksa timezone dan waktu
     public static function checkTimeInfo()
     {
         return [
             'app_timezone' => config('app.timezone'),
             'php_timezone' => date_default_timezone_get(),
             'carbon_timezone' => Carbon::now()->timezone->getName(),
             'current_time' => Carbon::now()->toDateTimeString(),
             'current_date' => Carbon::now()->toDateString(),
             'end_of_today' => Carbon::now()->endOfDay()->toDateTimeString(),
         ];
     }
 }