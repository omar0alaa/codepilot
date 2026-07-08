@extends('layouts.app')

@section('title', 'Settings - ' . $repository->full_name . ' - CodePilot AI')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('repositories.index') }}" class="hover:text-indigo-600">Repositories</a>
        <span>/</span>
        <a href="{{ route('repositories.show', $repository) }}" class="hover:text-indigo-600">{{ $repository->full_name }}</a>
        <span>/</span>
        <span class="text-gray-900">Settings</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">Repository Settings</h1>

    <!-- Review Settings -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Review Configuration</h2>
        <form action="{{ route('repositories.settings.update', $repository) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_enabled" id="is_enabled" value="1" {{ $repository->is_enabled ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_enabled" class="ml-2 text-sm text-gray-700">Enable AI reviews for this repository</label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Save Settings</button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="bg-white rounded-lg shadow p-6 border border-red-200">
        <h2 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h2>
        <p class="text-sm text-gray-600 mb-4">Removing this repository will disconnect it from CodePilot AI. You can reconnect it later.</p>
        <form action="{{ route('repositories.destroy', $repository) }}" method="POST" onsubmit="return confirm('Are you sure?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Remove Repository</button>
        </form>
    </div>
</div>
@endsection
