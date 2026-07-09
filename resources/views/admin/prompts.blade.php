@extends('layouts.app')
@section('title', 'Admin — Prompt Templates')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Prompt Templates</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">← Back</a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-600 mb-4">Prompt templates are managed in <code>app/Services/Ai/Prompts/PromptTemplateBuilder.php</code>. Versioned prompts will be available in a future update.</p>
        @if(!empty($templates))
            <div class="space-y-3">
                @foreach($templates as $name => $template)
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-900">{{ $name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ json_encode($template) }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No custom templates configured. Using default review prompt.</p>
        @endif
    </div>
</div>
@endsection