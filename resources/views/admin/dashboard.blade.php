@extends('layouts.app')

@section('title', 'Admin Dashboard - CodePilot AI')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500">Users</div>
            <div class="text-xl font-bold text-gray-900">{{ $stats['users'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500">Reviews</div>
            <div class="text-xl font-bold text-gray-900">{{ $stats['reviews'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500">Completed</div>
            <div class="text-xl font-bold text-green-600">{{ $stats['completed_reviews'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500">Failed</div>
            <div class="text-xl font-bold text-red-600">{{ $stats['failed_reviews'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500">Webhook Events</div>
            <div class="text-xl font-bold text-gray-900">{{ $stats['webhook_events'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500">Pending Webhooks</div>
            <div class="text-xl font-bold text-yellow-600">{{ $stats['pending_webhooks'] }}</div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <a href="{{ route('admin.users') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="text-2xl mb-2">👥</div>
            <div class="text-sm font-medium text-gray-900">Users</div>
        </a>
        <a href="{{ route('admin.ai-providers') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="text-2xl mb-2">🤖</div>
            <div class="text-sm font-medium text-gray-900">AI Providers</div>
        </a>
        <a href="{{ route('admin.prompts') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="text-2xl mb-2">📝</div>
            <div class="text-sm font-medium text-gray-900">Prompt Templates</div>
        </a>
        <a href="{{ route('admin.queue') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="text-2xl mb-2">⚙️</div>
            <div class="text-sm font-medium text-gray-900">Queue</div>
        </a>
        <a href="{{ route('admin.webhooks') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="text-2xl mb-2">🔗</div>
            <div class="text-sm font-medium text-gray-900">Webhooks</div>
        </a>
        <a href="{{ route('admin.settings') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="text-2xl mb-2">🔧</div>
            <div class="text-sm font-medium text-gray-900">Settings</div>
        </a>
    </div>
</div>
@endsection
