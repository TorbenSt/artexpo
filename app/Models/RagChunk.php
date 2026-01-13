<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RagChunk extends Model
{
    /** @use HasFactory<\Database\Factories\RagChunkFactory> */
    use HasFactory;

    protected $fillable = [
        'rag_document_id',
        'chunk_index',
        'chunk_text',
    ];

    /**
     * Get the document this chunk belongs to.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(RagDocument::class);
    }

    /**
     * Get the source through document.
     */
    public function source()
    {
        return $this->document()->source();
    }
}
