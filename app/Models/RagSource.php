<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RagSource extends Model
{
    /** @use HasFactory<\Database\Factories\RagSourceFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'base_url',
        'license_note',
        'is_allowed',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];

    /**
     * Get all documents from this source.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(RagDocument::class);
    }

    /**
     * Get all chunks through documents.
     */
    public function chunks(): HasMany
    {
        return $this->hasManyThrough(RagChunk::class, RagDocument::class);
    }
}
