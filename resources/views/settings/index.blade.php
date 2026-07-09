@extends('layouts.app')

@section('title', 'Settings - CodePilot AI')

@section('content')
<div class="space-y-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900">Settings</h1>

    <!-- AI Provider Settings -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">AI Provider</h2>
        <form action="{{ route('settings.ai-provider.update') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">Active Provider</label>
                    <select name="provider" id="provider" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="groq" {{ $defaultProvider === 'groq' ? 'selected' : '' }}>Groq (Fast)</option>
                        @foreach($providers as $key => $config)
                            @if($key !== 'groq')
                                <option value="{{ $key }}" {{ $defaultProvider === $key ? 'selected' : '' }}>{{ ucfirst($key) }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                    <input type="text" id="model" name="model" 
                        value="{{ config('ai.providers.' . $defaultProvider . '.model', '') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="e.g. llama3-70b-8192">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>

    <!-- Notification Settings -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Notifications</h2>
        <form action="{{ route('settings.notifications.update') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="email_notifications" id="email_notifications" value="1" 
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="email_notifications" class="ml-2 text-sm text-gray-700">Email notifications for completed reviews</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="browser_notifications" id="browser_notifications" value="1" 
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="browser_notifications" class="ml-2 text-sm text-gray-700">Browser notifications</label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>

    <!-- GitHub Connection -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">GitHub Connection</h2>
        <div class="flex items-center justify-between">
            <div>
                @if(auth()->user()->github_id)
                    <p class="text-sm text-gray-600">Connected as <span class="font-medium">{{ auth()->user()->github_username }}</span></p>
                @else
                    <p class="text-sm text-gray-600">Not connected to GitHub</p>
                @endif
            </div>
            <a href="{{ route('auth.github') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                {{ auth()->user()->github_id ? 'Reconnect' : 'Connect GitHub' }}
            </a>
        </div>
    </div>
</div>
@endsection
