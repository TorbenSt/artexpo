<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RagQueryLog extends Model
{
    /** @use HasFactory<\Database\Factories\RagQueryLogFactory> */
    use HasFactory;

    protected $fillable = [
        'image_id',
        'network',
        'query_text',
        'top_chunks',
        'chunks_found',
    ];

    protected $casts = [
        'top_chunks' => 'array',
    ];

    /**
     * Get the image this log entry is associated with.
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
