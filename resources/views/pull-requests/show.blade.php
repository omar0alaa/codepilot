@extends('layouts.app')

@section('title', 'PR #' . $pullRequest->number . ' - CodePilot AI')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('pull-requests.index') }}" class="hover:text-indigo-600">Pull Requests</a>
        <span>/</span>
        <span class="text-gray-900">#{{ $pullRequest->number }}</span>
    </div>

    <!-- PR Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $pullRequest->title }}</h1>
                <div class="mt-2 flex items-center space-x-3 text-sm text-gray-500">
                    <span class="px-2 py-0.5 rounded-full
                        @if($pullRequest->state === 'open') bg-green-100 text-green-800
                        @elseif($pullRequest->state === 'closed') bg-red-100 text-red-800
                        @else bg-purple-100 text-purple-800 @endif">
                        {{ ucfirst($pullRequest->state) }}
                    </span>
                    <span>{{ $pullRequest->head_branch }} → {{ $pullRequest->base_branch }}</span>
                    <a href="{{ $pullRequest->github_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">View on GitHub →</a>
                </div>
            </div>
            <form action="{{ route('pull-requests.re-review', $pullRequest) }}" method="POST">
                @csrf
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                    Re-run Review
                </button>
            </form>
        </div>
    </div>

    <!-- Reviews -->
    <div class="space-y-4">
        <h2 class="text-lg font-medium text-gray-900">Reviews ({{ $reviews->count() }})</h2>
        
        @foreach($reviews as $review)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($review->status === 'completed') bg-green-100 text-green-800
                            @elseif($review->status === 'processing') bg-yellow-100 text-yellow-800
                            @elseif($review->status === 'failed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($review->status) }}
                        </span>
                        @if($review->ai_provider)
                            <span class="text-xs text-gray-500">{{ $review->ai_provider }} / {{ $review->ai_model }}</span>
                        @endif
                        <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    @if($review->overall_score !== null)
                        <div class="text-2xl font-bold {{ $review->overall_score >= 80 ? 'text-green-600' : ($review->overall_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $review->overall_score }}/100
                        </div>
                    @endif
                </div>

                @if($review->summary)
                    <p class="text-sm text-gray-700 mb-4">{{ $review->summary }}</p>
                @endif

                @if($review->category_scores)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                        @foreach($review->category_scores as $category => $score)
                            <div class="bg-gray-50 rounded-md p-3">
                                <div class="text-xs text-gray-500">{{ ucfirst($category) }}</div>
                                <div class="text-lg font-bold text-gray-900">{{ $score }}/100</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($review->issues)
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Issues Found ({{ count($review->issues) }})</h3>
                        <div class="space-y-2">
                            @foreach($review->issues as $index => $issue)
                                <div class="border-l-4 p-3 rounded-r-md
                                    @if(($issue['severity'] ?? 'info') === 'critical') border-red-500 bg-red-50
                                    @elseif(($issue['severity'] ?? 'info') === 'warning') border-yellow-500 bg-yellow-50
                                    @else border-blue-500 bg-blue-50 @endif">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900">{{ $issue['title'] ?? 'Issue' }}</span>
                                        <span class="text-xs uppercase font-bold
                                            @if(($issue['severity'] ?? 'info') === 'critical') text-red-600
                                            @elseif(($issue['severity'] ?? 'info') === 'warning') text-yellow-600
                                            @else text-blue-600 @endif">
                                            {{ $issue['severity'] ?? 'info' }}
                                        </span>
                                    </div>
                                    @if(isset($issue['description']))
                                        <p class="mt-1 text-sm text-gray-600">{{ $issue['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
