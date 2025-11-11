@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Bilder verwalten</h1>
        <a href="{{ route('admin.images.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Neues Bild hochladen
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Typ</label>
                <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Alle Typen</option>
                    <option value="public" {{ request('type') === 'public' ? 'selected' : '' }}>Öffentlich</option>
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

            <div>
                <label class="block text-sm font-medium text-gray-700">Sichtbarkeit</label>
                <select name="visible" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Alle</option>
                    <option value="1" {{ request('visible') === '1' ? 'selected' : '' }}>Sichtbar</option>
                    <option value="0" {{ request('visible') === '0' ? 'selected' : '' }}>Versteckt</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Filtern
                </button>
            </div>
        </form>
    </div>

    <!-- Bilder Grid (Admin mit Bearbeitungsoptionen) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($images as $image)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <img src="{{ asset($image->path) }}" alt="Bild" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg">{{ $image->exhibition->title }}</h3>
                    <p class="text-gray-600 text-sm">Typ: {{ ucfirst($image->type) }}</p>
                    @if($image->position)
                        <p class="text-gray-600 text-sm">Position: {{ $image->position }}</p>
                    @endif
                    @if($image->credits)
                        <p class="text-gray-600 text-sm">Credits: {{ $image->credits }}</p>
                    @endif
                    <div class="flex justify-between items-center mt-4">
                        <span class="px-2 py-1 rounded text-xs {{ $image->visible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $image->visible ? 'Sichtbar' : 'Versteckt' }}
                        </span>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.images.edit', $image) }}" class="text-blue-600 hover:text-blue-900">Bearbeiten</a>
                            <form method="POST" action="{{ route('admin.images.destroy', $image) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" 
                                        onclick="return confirm('Sind Sie sicher?')">Löschen</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12">
                <p class="text-gray-500">Keine Bilder gefunden.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $images->links() }}
    </div>
</div>
@endsection