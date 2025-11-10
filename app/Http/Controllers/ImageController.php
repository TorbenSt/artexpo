<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Exhibition;
use App\Http\Requests\StoreImageRequest;
use App\Http\Requests\UpdateImageRequest;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImageRequest $request, Exhibition $exhibition)
    {
        $file = $request->file('image');
        $type = $request->input('type', 'public');
        $position = $request->input('position');
        $credits = $request->input('credits');
        $visible = $request->boolean('visible', true);

        // 1. Original (nur bei Presse)
        $originalPath = $type === 'press' ? $file->store('press/original', 'public') : null;

        // 2. Resized Version (1920px Breite, max)
        $manager = new ImageManager(new Driver());
        $img = $manager->read($file)->scaleDown(1920);

        $filename = Str::random(12) . '.' . $file->extension();
        $path = 'exhibitions/' . $exhibition->id . '/' . $filename;

        Storage::disk('public')->put($path, $img->encode());

        // 3. Speichern
        $image = Image::create([
            'exhibition_id' => $exhibition->id,
            'type' => $type,
            'path' => $path,
            'original_path' => $originalPath,
            'credits' => $credits,
            'visible' => $visible,
            'position' => $position,
        ]);

        return redirect()->back()->with('success', 'Bild hochgeladen');
    }

    /**
     * Display the specified resource.
     */
    public function show(Image $image)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Image $image)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateImageRequest $request, Image $image)
    {
        $data = $request->only(['position', 'credits', 'visible', 'type']);

        if ($request->hasFile('image')) {
            // Alte Dateien löschen
            if ($image->path) Storage::disk('public')->delete($image->path);
            if ($image->original_path) Storage::disk('public')->delete($image->original_path);

            $file = $request->file('image');
            $type = $request->input('type', $image->type);

            $originalPath = $type === 'press' ? $file->store('press/original', 'public') : null;

            $manager = new ImageManager(new Driver());
            $img = $manager->read($file)->scaleDown(1920);
            
            $filename = Str::random(12) . '.' . $file->extension();
            $path = 'exhibitions/' . $image->exhibition_id . '/' . $filename;
            Storage::disk('public')->put($path, $img->encode());

            $data['path'] = $path;
            $data['original_path'] = $originalPath;
            $data['type'] = $type;
        }

        $image->update($data);

        return redirect()->back()->with('success', 'Bild aktualisiert');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        $pathsToDelete = array_filter([$image->path, $image->original_path]);
        
        if (!empty($pathsToDelete)) {
            Storage::disk('public')->delete($pathsToDelete);
        }
        
        $image->delete();
        return redirect()->back()->with('success', 'Bild gelöscht');
    }
}
