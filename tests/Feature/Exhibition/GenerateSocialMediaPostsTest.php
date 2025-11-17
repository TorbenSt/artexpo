<?php

use App\Models\Exhibition;
use App\Models\Image;
use App\Models\User;
use App\Jobs\GenerateSocialMediaPostsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->exhibition = Exhibition::factory()->create([
        'title' => 'Test Exhibition',
        'artist' => 'Test Artist',
    ]);
});

describe('Social Media Post Generation', function () {
    test('unauthenticated user cannot generate social media posts', function () {
        $response = $this->post(
            route('admin.exhibitions.generate-social-media-posts', $this->exhibition)
        );
        
        $response->assertRedirect(route('login'));
    });
    
    test('authenticated user can trigger social media post generation', function () {
        Queue::fake();
        
        Image::factory()->create([
            'exhibition_id' => $this->exhibition->id,
            'for_social_media' => true,
        ]);
        
        $this->actingAs($this->user)
            ->post(route('admin.exhibitions.generate-social-media-posts', $this->exhibition))
            ->assertRedirect()
            ->assertSessionHas('success');
    });
    
    test('jobs are dispatched for all for_social_media marked images', function () {
        Queue::fake();
        
        $images = Image::factory(3)->create([
            'exhibition_id' => $this->exhibition->id,
            'for_social_media' => true,
        ]);
        
        // Create one image that should NOT be processed
        Image::factory()->create([
            'exhibition_id' => $this->exhibition->id,
            'for_social_media' => false,
        ]);
        
        $this->actingAs($this->user)
            ->post(route('admin.exhibitions.generate-social-media-posts', $this->exhibition));
        
        Queue::assertPushed(GenerateSocialMediaPostsJob::class, 3);
    });
    
    test('only images of the exhibition are processed', function () {
        Queue::fake();
        
        $otherExhibition = Exhibition::factory()->create();
        
        Image::factory(2)->create([
            'exhibition_id' => $this->exhibition->id,
            'for_social_media' => true,
        ]);
        
        // Create images for other exhibition
        Image::factory(2)->create([
            'exhibition_id' => $otherExhibition->id,
            'for_social_media' => true,
        ]);
        
        $this->actingAs($this->user)
            ->post(route('admin.exhibitions.generate-social-media-posts', $this->exhibition));
        
        Queue::assertPushed(GenerateSocialMediaPostsJob::class, 2);
    });
    
    test('visible flag does not affect image processing', function () {
        Queue::fake();
        
        Image::factory()->create([
            'exhibition_id' => $this->exhibition->id,
            'for_social_media' => true,
            'visible' => true,
        ]);
        
        Image::factory()->create([
            'exhibition_id' => $this->exhibition->id,
            'for_social_media' => true,
            'visible' => false,
        ]);
        
        $this->actingAs($this->user)
            ->post(route('admin.exhibitions.generate-social-media-posts', $this->exhibition));
        
        Queue::assertPushed(GenerateSocialMediaPostsJob::class, 2);
    });
    
    test('success message includes count of processed images', function () {
        Queue::fake();
        
        Image::factory(5)->create([
            'exhibition_id' => $this->exhibition->id,
            'for_social_media' => true,
        ]);
        
        $response = $this->actingAs($this->user)
            ->post(route('admin.exhibitions.generate-social-media-posts', $this->exhibition));
        
        $response->assertSessionHas('success', 'Social media post generation started for 5 image(s).');
    });
    
    test('no jobs are dispatched if no images are marked for social media', function () {
        Queue::fake();
        
        Image::factory(3)->create([
            'exhibition_id' => $this->exhibition->id,
            'for_social_media' => false,
        ]);
        
        $response = $this->actingAs($this->user)
            ->post(route('admin.exhibitions.generate-social-media-posts', $this->exhibition));
        
        Queue::assertPushed(GenerateSocialMediaPostsJob::class, 0);
        $response->assertSessionHas('success', 'Social media post generation started for 0 image(s).');
    });
});
