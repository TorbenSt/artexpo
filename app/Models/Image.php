<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /** @use HasFactory<\Database\Factories\ImageFactory> */
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 
        'type', 
        'path', 
        'original_path', 
        'credits', 
        'visible', 
        'position',
        'for_social_media'
    ];

    protected $casts = [
        'visible' => 'boolean',
        'for_social_media' => 'boolean',
    ];

    // Type: 'public' oder 'press'
    // Position: String-Feld für Erweiterbarkeit (z.B. 'StartSeiteSlide'), kann später als Enum erweitert werden

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
