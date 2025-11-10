<?php

use App\Models\Image;

test('image has correct fillable attributes', function () {
    $fillable = [
        'exhibition_id',
        'type',
        'path',
        'original_path',
        'credits',
        'visible',
        'position'
    ];
    
    $image = new Image();
    expect($image->getFillable())->toEqual($fillable);
});

test('image has correct casts', function () {
    $image = new Image();
    $casts = $image->getCasts();
    
    expect($casts)->toHaveKey('visible');
    expect($casts['visible'])->toBe('boolean');
});