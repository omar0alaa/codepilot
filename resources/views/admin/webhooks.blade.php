@extends('layouts.app')
@section('title', 'Admin — Webhooks')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Webhook Events</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">← Back</a>
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-200">
            @forelse($events as $event)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-900">{{ $event->event_type }} / {{ $event->action }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ $event->event_id }}</span>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($event->status === 'processed') bg-green-100 text-green-800
                            @elseif($event->status === 'processing') bg-yellow-100 text-yellow-800
                            @elseif($event->status === 'failed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $event->status }}
                        </span>
                    </div>
                    @if($event->repository)
                        <p class="text-xs text-gray-500 mt-1">{{ $event->repository->full_name }}</p>
                    @endif
                    @if($event->error_message)
                        <p class="text-xs text-red-600 mt-1">{{ $event->error_message }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $event->created_at }}</p>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">No webhook events.</div>
            @endforelse
        </div>
    </div>
    {{ $events->links() }}
</div>
@endsection