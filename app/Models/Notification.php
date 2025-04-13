<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'image_path',
        'read_at',
        'type',
        'data',
        'is_sent_email'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
        'is_sent_email' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsSent()
    {
        $this->update(['is_sent_email' => true]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
