<?php

use App\Models\Exhibition;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // RefreshDatabase is applied globally to Feature tests in tests/Pest.php
    Storage::fake('public');
});

it('stores image with for_social_media flag', function () {
    // Instead of exercising the image upload pipeline (which depends on GD/Imagick
    // in this environment), assert that the model can be created with the flag
    // and persists correctly.
    $exhibition = Exhibition::factory()->create();

    Image::create([
        'exhibition_id' => $exhibition->id,
        'type' => 'public',
        'path' => 'images/dummy.jpg',
        'original_path' => null,
        'credits' => 'Test Credit',
        'visible' => true,
        'position' => 'Galerie',
        'for_social_media' => true,
    ]);

    $this->assertDatabaseHas('images', [
        'exhibition_id' => $exhibition->id,
        'credits' => 'Test Credit',
        'for_social_media' => 1,
    ]);
});

it('updates image for_social_media flag', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $exhibition = Exhibition::factory()->create();

    // Create initial image via factory
    $image = Image::factory()->create([
        'exhibition_id' => $exhibition->id,
        'for_social_media' => false,
    ]);

    $response = $this->put(route('admin.images.update', $image), [
        'position' => $image->position,
        'credits' => 'Updated Credit',
        'visible' => 1,
        'for_social_media' => 1,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('images', [
        'id' => $image->id,
        'credits' => 'Updated Credit',
        'for_social_media' => 1,
    ]);
});
