<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 
        'thumbnail', 'avatar', 'bio', 'social', 'provider', 'provider_id'
    ];

    protected $casts = [
        'social' => 'array',
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function setPhoneAttribute($value)
{
    // Hapus semua karakter selain angka
    $phone = preg_replace('/\D/', '', $value);

    // Jika dimulai dengan "62", ubah ke "0"
    if (substr($phone, 0, 2) === '62') {
        $phone = '0' . substr($phone, 2);
    }

    // Simpan hasil akhirnya
    $this->attributes['phone'] = $phone;
}

public function isSocialAccount()
{
    return !empty($this->provider) && !empty($this->provider_id);
}

public function hasPassword()
{
    return !empty($this->password);
}

    
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function savedCampaigns()
{
    return $this->belongsToMany(Campaign::class, 'user_campaign_save');
}

    public function fundraisings()
    {
        return $this->hasMany(Fundraising::class);
    }

    public function fundraisingWithdrawals()
    {
        return $this->hasMany(FundraisingWithdrawal::class);
    }

    public function donationLikes()
    {
        return $this->hasMany(DonationLike::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

// Di model User.php
public function getAvatarUrlAttribute()
{
    return $this->avatar && !Str::startsWith($this->avatar, 'default/') 
        ? asset('storage/' . $this->avatar) 
        : asset('assets/img/' . $this->avatar);
}

public function getThumbnailUrlAttribute()
{
    return $this->thumbnail && !Str::startsWith($this->thumbnail, 'default/') 
        ? asset('storage/' . $this->thumbnail) 
        : asset('assets/img/' . $this->thumbnail);
}

}
