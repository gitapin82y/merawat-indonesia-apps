<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MootaBank extends Model
{
    use HasFactory;

    protected $table = 'moota_banks';

    protected $fillable = [
        'bank_id',
        'bank_type',
        'account_number',
        'account_name',
        'label',
        'balance',
        'is_active',
        'moota_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'moota_active'   => 'boolean',
        'balance'        => 'decimal:2',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Scope: hanya bank yang aktif ditampilkan ke donatur
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('moota_active', true);
    }

    /**
     * Label nama bank untuk tampilan
     */
    public function getBankLabelAttribute(): string
    {
        return $this->label ?: strtoupper($this->bank_type);
    }
}