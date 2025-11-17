<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Http\Requests\StoreExhibitionRequest;
use App\Http\Requests\UpdateExhibitionRequest;
use App\Jobs\GenerateSocialMediaPostsJob;
use Illuminate\Http\Request;

class ExhibitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exhibitions = Exhibition::orderBy('start_date', 'desc')->paginate(10);
        return view('exhibitions.index', compact('exhibitions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.exhibitions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExhibitionRequest $request)
    {
        $exhibition = Exhibition::create($request->validated());

        return redirect()->route('admin.exhibitions.edit', $exhibition)
            ->with('success', 'Exhibition created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Exhibition $exhibition)
    {
        return view('exhibitions.show', compact('exhibition'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exhibition $exhibition)
    {
        return view('admin.exhibitions.edit', compact('exhibition'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExhibitionRequest $request, Exhibition $exhibition)
    {
        $exhibition->update($request->validated());

        return redirect()->back()
            ->with('success', 'Exhibition updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exhibition $exhibition)
    {
        $exhibition->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Exhibition deleted successfully.');
    }

    /**
     * Generate social media posts for all marked images.
     */
    public function generateSocialMediaPosts(Exhibition $exhibition)
    {
        $images = $exhibition->images()->where('for_social_media', true)->get();
        
        $count = 0;
        foreach ($images as $image) {
            GenerateSocialMediaPostsJob::dispatch($image);
            $count++;
        }

        return redirect()->back()
            ->with('success', "Social media post generation started for {$count} image(s).");
    }
}
