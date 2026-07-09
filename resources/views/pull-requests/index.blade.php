@extends('layouts.app')

@section('title', 'Pull Requests - CodePilot AI')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Pull Requests</h1>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search by title or branch..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <select name="state" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All States</option>
                    <option value="open" {{ request('state') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('state') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">Filter</button>
        </form>
    </div>

    <!-- PR List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-200">
            @forelse($pullRequests as $pr)
                <div class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <a href="{{ route('pull-requests.show', $pr) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                #{{ $pr->number }} - {{ $pr->title }}
                            </a>
                            <div class="mt-1 flex items-center space-x-3 text-xs text-gray-500">
                                <a href="{{ route('repositories.show', $pr->repository) }}" class="hover:text-indigo-600">
                                    {{ $pr->repository->full_name }}
                                </a>
                                <span class="px-2 py-0.5 rounded-full
                                    @if($pr->state === 'open') bg-green-100 text-green-800
                                    @elseif($pr->state === 'closed') bg-red-100 text-red-800
                                    @else bg-purple-100 text-purple-800 @endif">
                                    {{ ucfirst($pr->state) }}
                                </span>
                                <span class="font-mono">{{ $pr->head_branch }} → {{ $pr->base_branch }}</span>
                                <span>{{ $pr->github_created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $pr->reviews->count() }} review(s)
                            @if($pr->reviews->where('status', 'completed')->isNotEmpty())
                                @php($latestReview = $pr->reviews->where('status', 'completed')->first())
                                <span class="ml-2 font-bold {{ $latestReview->overall_score >= 80 ? 'text-green-600' : ($latestReview->overall_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $latestReview->overall_score }}/100
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    No pull requests found.
                </div>
            @endforelse
        </div>
    </div>

    {{ $pullRequests->links() }}
</div>
@endsection
