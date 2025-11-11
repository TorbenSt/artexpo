@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Neue Ausstellung erstellen</h1>
            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Zurück zum Dashboard
            </a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.exhibitions.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Titel -->
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titel *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Untertitel -->
                    <div class="md:col-span-2">
                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Untertitel</label>
                        <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('subtitle')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Künstler -->
                    <div class="md:col-span-2">
                        <label for="artist" class="block text-sm font-medium text-gray-700 mb-2">Künstler</label>
                        <input type="text" name="artist" id="artist" value="{{ old('artist') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('artist')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Startdatum -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Startdatum *</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('start_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Enddatum -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Enddatum *</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('end_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Einführungstext -->
                    <div class="md:col-span-2">
                        <label for="intro_text" class="block text-sm font-medium text-gray-700 mb-2">Einführungstext</label>
                        <textarea name="intro_text" id="intro_text" rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Kurze Beschreibung der Ausstellung">{{ old('intro_text') }}</textarea>
                        @error('intro_text')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Haupttext -->
                    <div class="md:col-span-2">
                        <label for="text" class="block text-sm font-medium text-gray-700 mb-2">Beschreibung</label>
                        <textarea name="text" id="text" rows="6"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Ausführliche Beschreibung der Ausstellung">{{ old('text') }}</textarea>
                        @error('text')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ticket Link -->
                    <div class="md:col-span-2">
                        <label for="ticket_link" class="block text-sm font-medium text-gray-700 mb-2">Ticket-Link</label>
                        <input type="url" name="ticket_link" id="ticket_link" value="{{ old('ticket_link') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="https://...">
                        @error('ticket_link')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <a href="{{ route('dashboard') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Abbrechen
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Ausstellung erstellen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection