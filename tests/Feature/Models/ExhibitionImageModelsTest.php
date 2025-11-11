<?php

use App\Models\Exhibition;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->exhibition = Exhibition::factory()->create([
        'title' => 'Test Exhibition',
        'subtitle' => 'Test Subtitle',
        'artist' => 'Test Artist',
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-01',
    ]);
});

describe('Exhibition Model Database Tests', function () {
    test('exhibition dates are cast to carbon instances', function () {
        expect($this->exhibition->start_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($this->exhibition->end_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    test('exhibition can have many images', function () {
        $image1 = Image::factory()->create(['exhibition_id' => $this->exhibition->id]);
        $image2 = Image::factory()->create(['exhibition_id' => $this->exhibition->id]);
        
        $this->exhibition->refresh();
        expect($this->exhibition->images)->toHaveCount(2);
        expect($this->exhibition->images->pluck('id'))->toContain($image1->id);
        expect($this->exhibition->images->pluck('id'))->toContain($image2->id);
    });

    test('exhibition can get image by position', function () {
        $headerImage = Image::factory()->create([
            'exhibition_id' => $this->exhibition->id,
            'position' => 'Header'
        ]);
        
        $galleryImage = Image::factory()->create([
            'exhibition_id' => $this->exhibition->id,
            'position' => 'Galerie'
        ]);
        
        expect($this->exhibition->getImageByPosition('Header')->id)->toEqual($headerImage->id);
        expect($this->exhibition->getImageByPosition('Galerie')->id)->toEqual($galleryImage->id);
        expect($this->exhibition->getImageByPosition('NonExistent'))->toBeNull();
    });

    test('exhibition factory creates valid model', function () {
        $exhibition = Exhibition::factory()->create();
        
        expect($exhibition->title)->toBeString();
        expect($exhibition->start_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($exhibition->end_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($exhibition->start_date->isBefore($exhibition->end_date))->toBeTrue();
    });

    test('exhibition can be created with minimal data', function () {
        $exhibition = Exhibition::factory()->create([
            'title' => 'Minimal Exhibition',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'subtitle' => null,
            'artist' => null,
        ]);
        
        expect($exhibition->title)->toBe('Minimal Exhibition');
        expect($exhibition->subtitle)->toBeNull();
        expect($exhibition->artist)->toBeNull();
    });
});

describe('Image Model Database Tests', function () {
    beforeEach(function () {
        $this->image = Image::factory()->create([
            'exhibition_id' => $this->exhibition->id,
            'type' => 'public',
            'path' => 'exhibitions/1/test_image.jpg',
            'visible' => true,
            'position' => 'Header',
            'credits' => 'Test Photographer'
        ]);
    });

    test('image visible attribute is cast to boolean', function () {
        expect($this->image->visible)->toBeBool();
    });

    test('image belongs to exhibition', function () {
        expect($this->image->exhibition_id)->toBe($this->exhibition->id);
        expect($this->image->exhibition->id)->toBe($this->exhibition->id);
    });

    test('image can be public type without original path', function () {
        $publicImage = Image::factory()->public()->create([
            'exhibition_id' => $this->exhibition->id
        ]);
        
        expect($publicImage->type)->toBe('public');
        expect($publicImage->original_path)->toBeNull();
    });

    test('image can be press type with original path', function () {
        $pressImage = Image::factory()->press()->create([
            'exhibition_id' => $this->exhibition->id
        ]);
        
        expect($pressImage->type)->toBe('press');
        expect($pressImage->original_path)->toBeString();
        expect($pressImage->original_path)->toContain('images/original/');
    });

    test('image can have specific position', function () {
        $slideImage = Image::factory()->startPageSlide()->create([
            'exhibition_id' => $this->exhibition->id
        ]);
        
        $galleryImage = Image::factory()->gallery()->create([
            'exhibition_id' => $this->exhibition->id
        ]);
        
        expect($slideImage->position)->toBe('StartSeiteSlide');
        expect($galleryImage->position)->toBe('Galerie');
    });

    test('image can be hidden', function () {
        $hiddenImage = Image::factory()->hidden()->create([
            'exhibition_id' => $this->exhibition->id
        ]);
        
        expect($hiddenImage->visible)->toBeFalse();
    });

    test('image factory creates valid model', function () {
        $image = Image::factory()->create();
        
        expect($image->type)->toBeIn(['public', 'press']);
        expect($image->path)->toBeString();
        expect($image->visible)->toBeBool();
        expect($image->exhibition_id)->toBeInt();
    });

    test('image can exist without credits or position', function () {
        $minimalImage = Image::factory()->create([
            'exhibition_id' => $this->exhibition->id,
            'credits' => null,
            'position' => null
        ]);
        
        expect($minimalImage->credits)->toBeNull();
        expect($minimalImage->position)->toBeNull();
    });
});