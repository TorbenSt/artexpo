@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Ausstellungen</h1>
    </div>

    <!-- Ausstellungen Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($exhibitions as $exhibition)
            <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                <a href="{{ route('exhibitions.show', $exhibition) }}" class="block">
                    @if($exhibition->images()->where('visible', true)->first())
                        <img src="{{ asset($exhibition->images()->where('visible', true)->first()->path) }}" 
                             alt="{{ $exhibition->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">Kein Bild verfügbar</span>
                        </div>
                    @endif
                    <div class="p-6">
                        <h3 class="font-bold text-xl mb-2">{{ $exhibition->title }}</h3>
                        @if($exhibition->subtitle)
                            <p class="text-gray-600 mb-2">{{ $exhibition->subtitle }}</p>
                        @endif
                        <p class="text-gray-700 mb-3">{{ $exhibition->artist }}</p>
                        <div class="text-sm text-gray-500">
                            <p>{{ $exhibition->start_date->format('d.m.Y') }} - {{ $exhibition->end_date->format('d.m.Y') }}</p>
                        </div>
                        @if($exhibition->intro_text)
                            <p class="text-gray-600 mt-3 text-sm leading-relaxed">
                                {{ Str::limit($exhibition->intro_text, 150) }}
                            </p>
                        @endif
                    </div>
                </a>
            </div>
        @empty
            <div class="col-span-3 text-center py-12">
                <p class="text-gray-500">Keine Ausstellungen verfügbar.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $exhibitions->links() }}
    </div>
</div>
@endsection