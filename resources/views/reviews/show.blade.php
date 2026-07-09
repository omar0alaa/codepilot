@extends('layouts.app')

@section('title', 'Review Details - CodePilot AI')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('reviews.index') }}" class="hover:text-indigo-600">Reviews</a>
        <span>/</span>
        <span class="text-gray-900">Review #{{ $review->id }}</span>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $review->pullRequest->title }}</h1>
                <div class="mt-2 flex items-center space-x-3 text-sm text-gray-500">
                    <a href="{{ route('repositories.show', $review->pullRequest->repository) }}" class="hover:text-indigo-600">
                        {{ $review->pullRequest->repository->full_name }}
                    </a>
                    <span>#{{ $review->pullRequest->number }}</span>
                    <span>{{ $review->created_at->diffForHumans() }}</span>
                    @if($review->ai_provider)
                        <span class="text-gray-400">via {{ $review->ai_provider }} / {{ $review->ai_model }}</span>
                    @endif
                </div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold {{ $review->overall_score >= 80 ? 'text-green-600' : ($review->overall_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $review->overall_score ?? 'N/A' }}
                </div>
                <div class="text-xs text-gray-500">/ 100</div>
            </div>
        </div>

        @if($review->category_scores)
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            @foreach($review->category_scores as $category => $score)
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="text-xs text-gray-500 mb-1">{{ ucfirst($category) }}</div>
                    <div class="text-2xl font-bold {{ $score >= 80 ? 'text-green-600' : ($score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $score }}
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $score >= 80 ? 'bg-green-500' : ($score >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $score }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        @if($review->summary)
        <div class="mb-6 p-4 bg-indigo-50 rounded-lg">
            <h3 class="text-sm font-medium text-indigo-900 mb-2">Summary</h3>
            <p class="text-sm text-gray-700">{{ $review->summary }}</p>
        </div>
        @endif

        @if($review->issues && count($review->issues) > 0)
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Issues Found ({{ count($review->issues) }})</h3>
            <div class="space-y-4">
                @foreach($review->issues as $issue)
                    <div class="border-l-4 p-4 rounded-r-lg
                        @if(($issue['severity'] ?? 'info') === 'critical') border-red-500 bg-red-50
                        @elseif(($issue['severity'] ?? 'info') === 'warning') border-yellow-500 bg-yellow-50
                        @else border-blue-500 bg-blue-50 @endif">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-3">
                                <h4 class="text-sm font-bold text-gray-900">{{ $issue['title'] ?? 'Issue' }}</h4>
                                @if(isset($issue['category']))
                                    <span class="text-xs px-2 py-0.5 bg-white rounded-full text-gray-600">{{ $issue['category'] }}</span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs uppercase font-bold
                                    @if(($issue['severity'] ?? 'info') === 'critical') text-red-600
                                    @elseif(($issue['severity'] ?? 'info') === 'warning') text-yellow-600
                                    @else text-blue-600 @endif">
                                    {{ $issue['severity'] ?? 'info' }}
                                </span>
                                @if(isset($issue['confidence']))
                                    <span class="text-xs text-gray-400">{{ round($issue['confidence'] * 100) }}% confident</span>
                                @endif
                            </div>
                        </div>
                        @if(isset($issue['file']))
                            <div class="text-xs font-mono text-gray-500 mb-2">
                                📄 {{ $issue['file'] }} @if(isset($issue['line'])) : {{ $issue['line'] }} @endif
                            </div>
                        @endif
                        @if(isset($issue['description']))
                            <p class="text-sm text-gray-600 mb-2">{{ $issue['description'] }}</p>
                        @endif
                        @if(isset($issue['suggestion']) && $issue['suggestion'])
                            <div class="mt-2 p-3 bg-white rounded-md">
                                <p class="text-xs font-medium text-gray-700 mb-1">💡 Suggestion:</p>
                                <p class="text-sm text-gray-600">{{ $issue['suggestion'] }}</p>
                            </div>
                        @endif
                        @if(isset($issue['example_code']) && $issue['example_code'])
                            <pre class="mt-2 p-3 bg-gray-900 text-green-400 rounded-md text-xs overflow-x-auto"><code>{{ $issue['example_code'] }}</code></pre>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="p-8 bg-green-50 rounded-lg text-center">
            <p class="text-green-700">✅ No significant issues detected!</p>
        </div>
        @endif

        @if($review->suggestions && count($review->suggestions) > 0)
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Suggestions</h3>
            <ul class="space-y-2">
                @foreach($review->suggestions as $suggestion)
                    <li class="flex items-start space-x-2">
                        <span class="mt-1 w-2 h-2 rounded-full
                            @if(($suggestion['priority'] ?? 'medium') === 'high') bg-red-400
                            @elseif(($suggestion['priority'] ?? 'medium') === 'medium') bg-yellow-400
                            @else bg-blue-400 @endif"></span>
                        <div>
                            <span class="text-xs text-gray-400 uppercase">{{ $suggestion['priority'] ?? 'medium' }}</span>
                            <p class="text-sm text-gray-700">{{ $suggestion['description'] ?? '' }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
