@extends('layouts.app')
@section('title', 'Admin — Settings')
@section('content')
<div class="space-y-6 max-w-3xl">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">← Back</a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default AI Provider</label>
                    <select name="ai_provider" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="groq" {{ config('ai.default') === 'groq' ? 'selected' : '' }}>Groq</option>
                        <option value="openai" {{ config('ai.default') === 'openai' ? 'selected' : '' }}>OpenAI</option>
                        <option value="claude" {{ config('ai.default') === 'claude' ? 'selected' : '' }}>Claude</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Review Timeout (seconds)</label>
                    <input type="number" name="max_review_timeout" value="{{ config('ai.providers.groq.timeout', 300) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection