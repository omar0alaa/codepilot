@extends('layouts.app')

@section('title', 'Repositories - CodePilot AI')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Repositories</h1>
        <a href="{{ route('repositories.connect') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            Connect Repository
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-200">
            @forelse($repositories as $repository)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 0C4.477 0 0 4.477 0 10c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.866-.013-1.7-2.782.603-3.369-1.342-3.369-1.342-.454-1.155-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.482A10.019 10.019 0 0020 10c0-5.523-4.477-10-10-10z"/></svg>
                        <div>
                            <a href="{{ route('repositories.show', $repository) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                {{ $repository->full_name }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $repository->description ?? 'No description' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if($repository->is_private)
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012 2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        @endif
                        <span class="px-2 py-1 text-xs rounded-full {{ $repository->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $repository->is_enabled ? 'Active' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <p class="text-gray-500">No repositories connected.</p>
                    <a href="{{ route('repositories.connect') }}" class="mt-2 inline-block text-indigo-600 hover:text-indigo-900">Connect your first repository →</a>
                </div>
            @endforelse
        </div>
    </div>

    {{ $repositories->links() }}
</div>
@endsection
