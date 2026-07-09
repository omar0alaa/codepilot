@extends('layouts.app')
@section('title', 'Admin — AI Providers')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">AI Providers</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">← Back</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($providers as $name => $config)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ ucfirst($name) }}</h3>
                    @if($name === $default)
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                    @endif
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                    <p>Model: <code>{{ $config['model'] ?? 'N/A' }}</code></p>
                    <p>Timeout: {{ $config['timeout'] ?? 120 }}s</p>
                    <p>API Key: {{ isset($config['api_key']) && $config['api_key'] ? '✅ Configured' : '❌ Not set' }}</p>
                </div>
                @if($name !== $default)
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="ai_provider" value="{{ $name }}">
                        <button type="submit" class="text-indigo-600 text-sm hover:text-indigo-900">Set as default →</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection