@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Bildergalerie</h1>
    </div>

    <!-- Filter (nur öffentlich sichtbare) -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Typ</label>
                <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Alle Typen</option>
                    <option value="public" {{ request('type') === 'public' ? 'selected' : '' }}>Galerie</option>
                    <option value="press" {{ request('type') === 'press' ? 'selected' : '' }}>Presse</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Ausstellung</label>
                <select name="exhibition_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Alle Ausstellungen</option>
                    @foreach($exhibitions as $exhibition)
                        <option value="{{ $exhibition->id }}" {{ request('exhibition_id') == $exhibition->id ? 'selected' : '' }}>
                            {{ $exhibition->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Filtern
                </button>
            </div>
        </form>
    </div>

    <!-- Bilder Grid (nur öffentlich sichtbare) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($images as $image)
            <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                <a href="{{ route('images.show', $image) }}" class="block">
                    <img src="{{ asset($image->path) }}" alt="Bild" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg">{{ $image->exhibition->title }}</h3>
                        @if($image->position)
                            <p class="text-gray-600 text-sm">{{ $image->position }}</p>
                        @endif
                        @if($image->credits)
                            <p class="text-gray-500 text-xs mt-2">© {{ $image->credits }}</p>
                        @endif
                    </div>
                </a>
            </div>
        @empty
            <div class="col-span-4 text-center py-12">
                <p class="text-gray-500">Keine Bilder verfügbar.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $images->links() }}
    </div>
</div>
@endsection