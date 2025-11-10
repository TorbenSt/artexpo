<?php

use App\Http\Requests\StoreImageRequest;
use App\Http\Requests\UpdateImageRequest;

describe('StoreImageRequest', function () {
    test('validation rules are correct', function () {
        $request = new StoreImageRequest();
        $rules = $request->rules();
        
        expect($rules)->toHaveKey('image');
        expect($rules)->toHaveKey('type');
        expect($rules['image'])->toContain('required');
        expect($rules['image'])->toContain('image');
        expect($rules['type'])->toContain('required');
        expect($rules['type'])->toContain('in:public,press');
    });
    
    test('authorization returns true', function () {
        $request = new StoreImageRequest();
        
        expect($request->authorize())->toBeTrue();
    });
});

describe('UpdateImageRequest', function () {
    test('validation rules are correct', function () {
        $request = new UpdateImageRequest();
        $rules = $request->rules();
        
        expect($rules)->toHaveKey('image');
        expect($rules)->toHaveKey('type');
        expect($rules['image'])->toContain('nullable');
        expect($rules['image'])->toContain('image');
        expect($rules['type'])->toContain('sometimes');
        expect($rules['type'])->toContain('in:public,press');
    });
    
    test('authorization returns true', function () {
        $request = new UpdateImageRequest();
        
        expect($request->authorize())->toBeTrue();
    });
});