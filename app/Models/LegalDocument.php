<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'content',
        'last_updated',
    ];

    // Constants for document types
    public const PRIVACY_POLICY = 'privacy_policy';
    public const TERMS_OF_SERVICE = 'terms_of_service';

    /**
     * Get document by type
     * 
     * @param string $type
     * @return LegalDocument|null
     */
    public static function getByType($type)
    {
        return self::where('type', $type)->first();
    }
}