@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="text-gray-600">Willkommen, {{ auth()->user()->name }}!</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Ausstellungen</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ App\Models\Exhibition::count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Bilder</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ App\Models\Image::count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Sichtbare Bilder</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ App\Models\Image::where('visible', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Ausstellungen verwalten</h2>
                <div class="space-y-3">
                    <a href="{{ route('admin.exhibitions.create') }}" 
                       class="block w-full bg-blue-500 hover:bg-blue-700 text-white text-center font-bold py-2 px-4 rounded">
                        Neue Ausstellung erstellen
                    </a>
                    <a href="{{ route('exhibitions.index') }}" 
                       class="block w-full bg-gray-500 hover:bg-gray-700 text-white text-center font-bold py-2 px-4 rounded">
                        Alle Ausstellungen anzeigen
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Bilder verwalten</h2>
                <div class="space-y-3">
                    <a href="{{ route('admin.images.create') }}" 
                       class="block w-full bg-green-500 hover:bg-green-700 text-white text-center font-bold py-2 px-4 rounded">
                        Neues Bild hochladen
                    </a>
                    <a href="{{ route('images.index') }}" 
                       class="block w-full bg-gray-500 hover:bg-gray-700 text-white text-center font-bold py-2 px-4 rounded">
                        Alle Bilder anzeigen
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Letzte Aktivitäten</h2>
            <div class="space-y-4">
                @php
                    $recentExhibitions = App\Models\Exhibition::latest()->take(3)->get();
                    $recentImages = App\Models\Image::with('exhibition')->latest()->take(5)->get();
                @endphp

                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Neueste Ausstellungen</h3>
                    @if($recentExhibitions->count() > 0)
                        <div class="space-y-2">
                            @foreach($recentExhibitions as $exhibition)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="font-medium">{{ $exhibition->title }}</span>
                                    <a href="{{ route('admin.exhibitions.edit', $exhibition) }}" 
                                       class="text-blue-600 hover:text-blue-900">Bearbeiten</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Noch keine Ausstellungen erstellt.</p>
                    @endif
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Neueste Bilder</h3>
                    @if($recentImages->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                            @foreach($recentImages as $image)
                                <div class="relative">
                                    <img src="{{ asset('storage/' . $image->path) }}" 
                                         alt="Recent image" 
                                         class="w-full h-20 object-cover rounded">
                                    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b">
                                        {{ $image->exhibition->title ?? 'Ohne Ausstellung' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Noch keine Bilder hochgeladen.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
