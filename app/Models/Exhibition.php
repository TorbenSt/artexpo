<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exhibition extends Model
{
    /** @use HasFactory<\Database\Factories\ExhibitionFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'intro_text',
        'text',
        'artist',
        'start_date',
        'end_date',
        'program_booklet',
        'program_booklet_cover',
        'flyer',
        'flyer_cover',
        'creative_booklet',
        'creative_booklet_cover',
        'ticket_link'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    // Hilfsfunktion für Bilder nach Position
    public function getImageByPosition($position)
    {
        return $this->images()->where('position', $position)->first();
    }
}
