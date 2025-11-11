@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">{{ $exhibition->title }} bearbeiten</h1>
            <div class="flex space-x-2">
                <a href="{{ route('exhibitions.show', $exhibition) }}" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Vorschau
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Zurück zum Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Hauptformular -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Ausstellungsdaten</h2>
                    <form method="POST" action="{{ route('admin.exhibitions.update', $exhibition) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Titel -->
                            <div class="md:col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titel *</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $exhibition->title) }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('title')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Untertitel -->
                            <div class="md:col-span-2">
                                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Untertitel</label>
                                <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $exhibition->subtitle) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('subtitle')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Künstler -->
                            <div class="md:col-span-2">
                                <label for="artist" class="block text-sm font-medium text-gray-700 mb-2">Künstler</label>
                                <input type="text" name="artist" id="artist" value="{{ old('artist', $exhibition->artist) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('artist')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Startdatum -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Startdatum *</label>
                                <input type="date" name="start_date" id="start_date" 
                                       value="{{ old('start_date', $exhibition->start_date?->format('Y-m-d')) }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('start_date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Enddatum -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Enddatum *</label>
                                <input type="date" name="end_date" id="end_date" 
                                       value="{{ old('end_date', $exhibition->end_date?->format('Y-m-d')) }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('end_date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Einführungstext -->
                            <div class="md:col-span-2">
                                <label for="intro_text" class="block text-sm font-medium text-gray-700 mb-2">Einführungstext</label>
                                <textarea name="intro_text" id="intro_text" rows="3"
                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('intro_text', $exhibition->intro_text) }}</textarea>
                                @error('intro_text')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Haupttext -->
                            <div class="md:col-span-2">
                                <label for="text" class="block text-sm font-medium text-gray-700 mb-2">Beschreibung</label>
                                <textarea name="text" id="text" rows="6"
                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('text', $exhibition->text) }}</textarea>
                                @error('text')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Ticket Link -->
                            <div class="md:col-span-2">
                                <label for="ticket_link" class="block text-sm font-medium text-gray-700 mb-2">Ticket-Link</label>
                                <input type="url" name="ticket_link" id="ticket_link" 
                                       value="{{ old('ticket_link', $exhibition->ticket_link) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('ticket_link')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <form method="POST" action="{{ route('admin.exhibitions.destroy', $exhibition) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                        onclick="return confirm('Sind Sie sicher? Diese Aktion kann nicht rückgängig gemacht werden.')">
                                    Ausstellung löschen
                                </button>
                            </form>

                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Änderungen speichern
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Bilder verwalten -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Bilder verwalten</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.images.create', ['exhibition_id' => $exhibition->id]) }}" 
                           class="block w-full bg-blue-500 hover:bg-blue-700 text-white text-center font-bold py-2 px-4 rounded">
                            Neues Bild hochladen
                        </a>
                        <a href="{{ route('images.index', ['exhibition_id' => $exhibition->id]) }}" 
                           class="block w-full bg-gray-500 hover:bg-gray-700 text-white text-center font-bold py-2 px-4 rounded">
                            Alle Bilder anzeigen
                        </a>
                    </div>
                </div>

                <!-- Aktuelle Bilder -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Aktuelle Bilder ({{ $exhibition->images->count() }})</h3>
                    @if($exhibition->images->count() > 0)
                        <div class="space-y-2">
                            @foreach($exhibition->images->take(3) as $image)
                                <div class="flex items-center space-x-2">
                                    <img src="{{ asset($image->path) }}" 
                                         class="w-12 h-12 object-cover rounded">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900 truncate">{{ $image->position ?: 'Ohne Position' }}</p>
                                        <p class="text-xs text-gray-500">{{ $image->type }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded {{ $image->visible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $image->visible ? 'Sichtbar' : 'Versteckt' }}
                                    </span>
                                </div>
                            @endforeach
                            @if($exhibition->images->count() > 3)
                                <p class="text-sm text-gray-500 text-center pt-2">
                                    und {{ $exhibition->images->count() - 3 }} weitere...
                                </p>
                            @endif
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