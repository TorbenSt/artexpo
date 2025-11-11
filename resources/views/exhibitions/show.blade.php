@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-4xl font-bold mb-2">{{ $exhibition->title }}</h1>
                @if($exhibition->subtitle)
                    <h2 class="text-2xl text-gray-600 mb-4">{{ $exhibition->subtitle }}</h2>
                @endif
                <p class="text-xl text-gray-700 mb-2">{{ $exhibition->artist }}</p>
                <p class="text-gray-600">
                    {{ $exhibition->start_date->format('d.m.Y') }} - {{ $exhibition->end_date->format('d.m.Y') }}
                </p>
            </div>
            <div class="flex space-x-2">
                @auth
                    <a href="{{ route('admin.exhibitions.edit', $exhibition) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Bearbeiten
                    </a>
                @endauth
                <a href="{{ route('exhibitions.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Zurück zur Übersicht
                </a>
            </div>
        </div>

        @if($exhibition->intro_text)
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <p class="text-lg text-gray-800 leading-relaxed">{{ $exhibition->intro_text }}</p>
            </div>
        @endif

        <!-- Hauptbild -->
        @if($exhibition->images()->where('visible', true)->where('position', 'hero')->first())
            <div class="mb-8">
                @php $heroImage = $exhibition->images()->where('visible', true)->where('position', 'hero')->first(); @endphp
                <img src="{{ asset('storage/' . $heroImage->path) }}" 
                     alt="{{ $exhibition->title }}" 
                     class="w-full max-h-96 object-cover rounded-lg shadow-lg">
                @if($heroImage->credits)
                    <p class="text-sm text-gray-500 mt-2">© {{ $heroImage->credits }}</p>
                @endif
            </div>
        @endif

        @if($exhibition->text)
            <div class="prose max-w-none mb-8">
                <div class="text-gray-800 leading-relaxed">
                    {!! nl2br(e($exhibition->text)) !!}
                </div>
            </div>
        @endif

        <!-- Bildergalerie -->
        @php $galleryImages = $exhibition->images()->where('visible', true)->where('position', '!=', 'hero')->get(); @endphp
        @if($galleryImages->count() > 0)
            <div class="mb-8">
                <h3 class="text-2xl font-bold mb-4">Bildergalerie</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($galleryImages as $image)
                        <div class="group">
                            <img src="{{ asset('storage/' . $image->path) }}" 
                                 alt="Ausstellungsbild" 
                                 class="w-full h-48 object-cover rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer">
                            @if($image->credits)
                                <p class="text-xs text-gray-500 mt-1">© {{ $image->credits }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Downloads und Links -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-xl font-bold mb-4">Downloads & Links</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($exhibition->program_booklet)
                    <a href="{{ asset('storage/' . $exhibition->program_booklet) }}" 
                       class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50" 
                       target="_blank">
                        <div class="mr-3">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span>Programmheft herunterladen</span>
                    </a>
                @endif

                @if($exhibition->flyer)
                    <a href="{{ asset('storage/' . $exhibition->flyer) }}" 
                       class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50" 
                       target="_blank">
                        <div class="mr-3">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span>Flyer herunterladen</span>
                    </a>
                @endif

                @if($exhibition->creative_booklet)
                    <a href="{{ asset('storage/' . $exhibition->creative_booklet) }}" 
                       class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50" 
                       target="_blank">
                        <div class="mr-3">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span>Creative Booklet herunterladen</span>
                    </a>
                @endif

                @if($exhibition->ticket_link)
                    <a href="{{ $exhibition->ticket_link }}" 
                       class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50" 
                       target="_blank">
                        <div class="mr-3">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                            </svg>
                        </div>
                        <span>Tickets kaufen</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection