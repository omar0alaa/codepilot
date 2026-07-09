@extends('layouts.app')

@section('title', 'Reviews - CodePilot AI')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Reviews</h1>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex gap-4">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-md">
                <option value="">All Statuses</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-200">
            @forelse($reviews as $review)
                <div class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <a href="{{ route('reviews.show', $review) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                {{ $review->pullRequest->title }}
                            </a>
                            <div class="mt-1 flex items-center space-x-3 text-xs text-gray-500">
                                <a href="{{ route('repositories.show', $review->pullRequest->repository) }}">
                                    {{ $review->pullRequest->repository->full_name }}
                                </a>
                                <span>#{{ $review->pullRequest->number }}</span>
                                <span>by {{ $review->pullRequest->repository->user->name }}</span>
                                <span>{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            @if($review->ai_provider)
                                <span class="text-xs text-gray-400">{{ $review->ai_provider }}</span>
                            @endif
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($review->status === 'completed') bg-green-100 text-green-800
                                @elseif($review->status === 'processing') bg-yellow-100 text-yellow-800
                                @elseif($review->status === 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($review->status) }}
                            </span>
                            @if($review->overall_score !== null)
                                <span class="text-sm font-bold {{ $review->overall_score >= 80 ? 'text-green-600' : ($review->overall_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $review->overall_score }}/100
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">No reviews found.</div>
            @endforelse
        </div>
    </div>

    {{ $reviews->links() }}
</div>
@endsection
