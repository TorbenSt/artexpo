<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro_text' => 'nullable|string',
            'text' => 'nullable|string',
            'artist' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'program_booklet' => 'nullable|url',
            'program_booklet_cover' => 'nullable|url',
            'flyer' => 'nullable|url',
            'flyer_cover' => 'nullable|url',
            'creative_booklet' => 'nullable|url',
            'creative_booklet_cover' => 'nullable|url',
            'ticket_link' => 'nullable|url',
        ]);

        $exhibition = Exhibition::create($validated);

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
    public function update(Request $request, Exhibition $exhibition)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro_text' => 'nullable|string',
            'text' => 'nullable|string',
            'artist' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'program_booklet' => 'nullable|url',
            'program_booklet_cover' => 'nullable|url',
            'flyer' => 'nullable|url',
            'flyer_cover' => 'nullable|url',
            'creative_booklet' => 'nullable|url',
            'creative_booklet_cover' => 'nullable|url',
            'ticket_link' => 'nullable|url',
        ]);

        $exhibition->update($validated);

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
}
