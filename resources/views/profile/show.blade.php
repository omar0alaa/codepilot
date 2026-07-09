@extends('layouts.app')

@section('title', 'Profile - CodePilot AI')

@section('content')
<div class="space-y-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900">Profile</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center space-x-4 mb-6">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-16 h-16 rounded-full">
            @else
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold text-indigo-600">{{ substr($user->name, 0, 1) }}</span>
                </div>
            @endif
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                @if($user->github_username)
                    <a href="https://github.com/{{ $user->github_username }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-900">
                        @{{ $user->github_username }} on GitHub →
                    </a>
                @endif
            </div>
        </div>

        <div class="border-t pt-4">
            <a href="{{ route('profile.edit') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                Edit Profile
            </a>
        </div>
    </div>
</div>
@endsection
