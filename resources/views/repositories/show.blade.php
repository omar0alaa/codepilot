@extends('layouts.app')

@section('title', $repository->full_name . ' - CodePilot AI')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('repositories.index') }}" class="hover:text-indigo-600">Repositories</a>
        <span>/</span>
        <span class="text-gray-900">{{ $repository->full_name }}</span>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $repository->full_name }}</h1>
            <p class="mt-1 text-gray-600">{{ $repository->description ?? 'No description' }}</p>
            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                <span>Default branch: {{ $repository->default_branch }}</span>
                @if($repository->is_private)
                    <span class="text-yellow-600">Private</span>
                @else
                    <span class="text-green-600">Public</span>
                @endif
                <span class="px-2 py-0.5 rounded-full text-xs {{ $repository->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $repository->is_enabled ? 'Reviews Active' : 'Reviews Disabled' }}
                </span>
            </div>
        </div>
        <div class="flex space-x-2">
            @if($repository->is_enabled)
                <form action="{{ route('github-app.disable', $repository) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Disable Reviews</button>
                </form>
            @else
                <form action="{{ route('github-app.enable', $repository) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Enable Reviews</button>
                </form>
            @endif
            <a href="{{ route('repositories.settings', $repository) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Settings</a>
        </div>
    </div>

    <!-- Pull Requests -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Pull Requests</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($pullRequests as $pr)
                <div class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <a href="{{ route('pull-requests.show', $pr) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                #{{ $pr->number }} - {{ $pr->title }}
                            </a>
                            <div class="mt-1 flex items-center space-x-3 text-xs text-gray-500">
                                <span class="px-2 py-0.5 rounded-full
                                    @if($pr->state === 'open') bg-green-100 text-green-800
                                    @elseif($pr->state === 'closed') bg-red-100 text-red-800
                                    @else bg-purple-100 text-purple-800 @endif">
                                    {{ ucfirst($pr->state) }}
                                </span>
                                <span>{{ $pr->head_branch }} → {{ $pr->base_branch }}</span>
                                <span>{{ $pr->github_created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $pr->reviews->count() }} review(s)
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    No pull requests found for this repository.
                </div>
            @endforelse
        </div>
    </div>

    {{ $pullRequests->links() }}
</div>
@endsection
