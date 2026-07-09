@extends('layouts.app')
@section('title', 'Admin — Queue')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Queue & Failed Jobs</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">← Back</a>
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Failed Jobs ({{ $failedJobs->count() }})</h2>
        </div>
        @if($failedJobs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Queue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Failed At</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($failedJobs as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $job->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $job->queue }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $job->display_name }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 max-w-xs truncate">{{ $job->exception }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $job->failed_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center text-gray-500">No failed jobs.</div>
        @endif
    </div>
</div>
@endsection