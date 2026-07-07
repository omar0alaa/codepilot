@extends('layouts.app')

@section('title', 'Dashboard - CodePilot AI')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <a href="{{ route('repositories.connect') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            Connect Repository
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Repositories</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['repositories'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Pull Requests</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['pull_requests'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Reviews</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['reviews'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Avg Score</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ round($stats['avg_score']) }}/100</div>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Recent Reviews</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($recentReviews as $review)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <a href="{{ route('reviews.show', $review) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                            {{ $review->pullRequest->title }}
                        </a>
                        <p class="text-sm text-gray-500">{{ $review->pullRequest->repository->full_name }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($review->status === 'completed') bg-green-100 text-green-800
                            @elseif($review->status === 'processing') bg-yellow-100 text-yellow-800
                            @elseif($review->status === 'failed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($review->status) }}
                        </span>
                        @if($review->overall_score)
                            <span class="text-sm font-bold {{ $review->overall_score >= 80 ? 'text-green-600' : ($review->overall_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $review->overall_score }}/100
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    No reviews yet. Connect a repository to get started.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Connected Repositories -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Connected Repositories</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($repositories as $repo)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <a href="{{ route('repositories.show', $repo) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                            {{ $repo->full_name }}
                        </a>
                        <p class="text-sm text-gray-500">{{ $repo->description ?? 'No description' }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full {{ $repo->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $repo->is_enabled ? 'Active' : 'Disabled' }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    No repositories connected.
                    <a href="{{ route('repositories.connect') }}" class="text-indigo-600 hover:text-indigo-900">Connect one now →</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
