@extends('layouts.app')

@section('title', 'Edit Profile - CodePilot AI')

@section('content')
<div class="space-y-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Save</button>
                <a href="{{ route('profile.show') }}" class="ml-2 text-gray-600 px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border border-red-200">
        <h2 class="text-lg font-medium text-red-900 mb-4">Delete Account</h2>
        <p class="text-sm text-gray-600 mb-4">This action is permanent and cannot be undone.</p>
        <form action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Are you sure?')">
            @csrf
            @method('DELETE')
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Enter your password to confirm</label>
                <input type="password" id="password" name="password" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Password">
            </div>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Delete Account</button>
        </form>
    </div>
</div>
@endsection
