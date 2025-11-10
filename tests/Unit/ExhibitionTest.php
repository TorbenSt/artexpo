<?php

use App\Models\Exhibition;

test('exhibition has correct fillable attributes', function () {
    $fillable = [
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
    
    $exhibition = new Exhibition();
    expect($exhibition->getFillable())->toEqual($fillable);
});

test('exhibition has correct casts', function () {
    $exhibition = new Exhibition();
    $casts = $exhibition->getCasts();
    
    expect($casts)->toHaveKey('start_date');
    expect($casts)->toHaveKey('end_date');
    expect($casts['start_date'])->toBe('date');
    expect($casts['end_date'])->toBe('date');
});