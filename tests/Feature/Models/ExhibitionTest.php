<?php

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->exhibition = Exhibition::factory()->create([
        'title' => 'Test Exhibition',
        'artist' => 'Test Artist',
    ]);
});

describe('Public Exhibition Routes', function () {
    test('can view exhibitions index page', function () {
        Exhibition::factory(3)->create();
        
        $response = $this->get(route('exhibitions.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('exhibitions.index');
    });
    
    test('can view single exhibition', function () {
        $response = $this->get(route('exhibitions.show', $this->exhibition));
        
        $response->assertStatus(200);
        $response->assertViewIs('exhibitions.show');
    });
});

describe('Admin Exhibition Routes', function () {
    test('unauthenticated user cannot access admin exhibition routes', function () {
        $routes = [
            ['GET', route('admin.exhibitions.create')],
            ['POST', route('admin.exhibitions.store')],
            ['GET', route('admin.exhibitions.edit', $this->exhibition)],
            ['PUT', route('admin.exhibitions.update', $this->exhibition)],
            ['DELETE', route('admin.exhibitions.destroy', $this->exhibition)],
        ];
        
        foreach ($routes as [$method, $url]) {
            $response = $this->{strtolower($method)}($url);
            $response->assertRedirect(route('login'));
        }
    });
    
    test('authenticated user can access admin exhibition create and edit routes', function () {
        $this->actingAs($this->user);
        
        // Test create form
        $response = $this->get(route('admin.exhibitions.create'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.exhibitions.create');
        
        // Test edit form
        $response = $this->get(route('admin.exhibitions.edit', $this->exhibition));
        $response->assertStatus(200);
        $response->assertViewIs('admin.exhibitions.edit');
    });
});

describe('Exhibition CRUD Operations', function () {
    beforeEach(function () {
        $this->actingAs($this->user);
    });
    
    test('can store new exhibition', function () {
        $exhibitionData = [
            'title' => 'New Exhibition',
            'subtitle' => 'New Subtitle',
            'intro_text' => 'Introduction text',
            'text' => 'Main exhibition text',
            'artist' => 'New Artist',
            'start_date' => '2025-06-01',
            'end_date' => '2025-08-01',
            'ticket_link' => 'https://tickets.example.com',
        ];
        
        $response = $this->post(route('admin.exhibitions.store'), $exhibitionData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('exhibitions', [
            'title' => 'New Exhibition',
            'artist' => 'New Artist',
        ]);
    });
    
    test('can update existing exhibition', function () {
        $updateData = [
            'title' => 'Updated Exhibition Title',
            'artist' => 'Updated Artist',
            'start_date' => $this->exhibition->start_date->format('Y-m-d'),
            'end_date' => $this->exhibition->end_date->format('Y-m-d'),
        ];
        
        $response = $this->put(route('admin.exhibitions.update', $this->exhibition), $updateData);
        
        $response->assertRedirect();
        $this->exhibition->refresh();
        
        expect($this->exhibition->title)->toBe('Updated Exhibition Title');
        expect($this->exhibition->artist)->toBe('Updated Artist');
    });
    
    test('can delete exhibition', function () {
        $response = $this->delete(route('admin.exhibitions.destroy', $this->exhibition));
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('exhibitions', [
            'id' => $this->exhibition->id,
        ]);
    });
    
    test('exhibition validation works for required fields', function () {
        $response = $this->post(route('admin.exhibitions.store'), []);
        
        $response->assertSessionHasErrors(['title', 'start_date', 'end_date']);
    });
    
    test('exhibition validation works for date validation', function () {
        $exhibitionData = [
            'title' => 'Test Exhibition',
            'start_date' => '2025-08-01',
            'end_date' => '2025-06-01', // End date before start date
        ];
        
        $response = $this->post(route('admin.exhibitions.store'), $exhibitionData);
        
        $response->assertSessionHasErrors(['end_date']);
    });
});