<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EspayPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'pay_method',
        'pay_option',
        'category',
        'fee_amount',
        'fee_type',
        'is_active',
        'icon_url',
        'description',
        'additional_info'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fee_amount' => 'decimal:2',
        'additional_info' => 'array'
    ];

    /**
     * Scope untuk filter metode pembayaran yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan kategori
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get formatted fee amount
     */
    public function getFormattedFeeAttribute()
    {
        if ($this->fee_type === 'percent') {
            return $this->fee_amount . '%';
        }
        return 'Rp ' . number_format($this->fee_amount, 0, ',', '.');
    }
}