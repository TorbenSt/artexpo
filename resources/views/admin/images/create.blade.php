@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Neues Bild hochladen</h1>
            <a href="{{ route('images.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Zurück
            </a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.images.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label for="exhibition_id" class="block text-sm font-medium text-gray-700 mb-2">Ausstellung *</label>
                    <select name="exhibition_id" id="exhibition_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                        <option value="">Ausstellung wählen</option>
                        @foreach($exhibitions as $exhibition)
                            <option value="{{ $exhibition->id }}" 
                                    {{ $selectedExhibition == $exhibition->id ? 'selected' : '' }}>
                                {{ $exhibition->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('exhibition_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Bild *</label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/jpg"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           required>
                    <p class="text-gray-500 text-sm mt-1">Erlaubte Formate: JPEG, PNG, JPG. Max. 10MB.</p>
                    @error('image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Typ *</label>
                    <select name="type" id="type" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                        <option value="public" {{ old('type', 'public') === 'public' ? 'selected' : '' }}>Öffentlich</option>
                        <option value="press" {{ old('type') === 'press' ? 'selected' : '' }}>Presse</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                    <input type="text" name="position" id="position" value="{{ old('position') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="z.B. StartSeiteSlide, Header, etc.">
                    <p class="text-gray-500 text-sm mt-1">Optional: Spezifische Position für das Bild.</p>
                    @error('position')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="credits" class="block text-sm font-medium text-gray-700 mb-2">Bildnachweis</label>
                    <textarea name="credits" id="credits" rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Fotograf, Copyright-Inhaber, etc.">{{ old('credits') }}</textarea>
                    @error('credits')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="visible" value="1" 
                               {{ old('visible', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Bild ist sichtbar</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('images.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Abbrechen
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Bild hochladen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection