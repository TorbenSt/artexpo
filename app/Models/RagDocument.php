<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RagDocument extends Model
{
    /** @use HasFactory<\Database\Factories\RagDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'rag_source_id',
        'title',
        'url',
        'language',
        'raw_text',
        'checksum',
        'retrieved_at',
        'artist_name',
        'artwork_title',
        'exhibition_title',
    ];

    protected $casts = [
        'retrieved_at' => 'datetime',
    ];

    /**
     * Get the source this document belongs to.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(RagSource::class);
    }

    /**
     * Get all chunks of this document.
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(RagChunk::class);
    }

    /**
     * Calculate SHA256 checksum of raw_text.
     */
    public static function generateChecksum(string $text): string
    {
        return hash('sha256', $text);
    }
}
