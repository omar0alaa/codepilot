@extends('layouts.app')

@section('title', 'Analytics - CodePilot AI')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>

    <!-- Developer Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Total PRs</div>
            <div class="text-2xl font-bold text-gray-900">{{ $developerMetrics['total_prs'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Total Reviews</div>
            <div class="text-2xl font-bold text-gray-900">{{ $developerMetrics['total_reviews'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Avg Score</div>
            <div class="text-2xl font-bold text-gray-900">{{ $developerMetrics['avg_score'] }}/100</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Reviews/Week</div>
            <div class="text-2xl font-bold text-gray-900">{{ $developerMetrics['review_frequency'] }}</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Quality Trend (Last 30 Days)</h2>
            <canvas id="qualityTrendChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Security Trend</h2>
            <canvas id="securityTrendChart" height="200"></canvas>
        </div>
    </div>

    <!-- Technical Debt -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Technical Debt</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-red-50 rounded-lg p-4">
                <div class="text-sm text-red-700">Critical Issues</div>
                <div class="text-2xl font-bold text-red-900">{{ $technicalDebt['critical'] }}</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-sm text-yellow-700">Warning Issues</div>
                <div class="text-2xl font-bold text-yellow-900">{{ $technicalDebt['warning'] }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-sm text-blue-700">Info Issues</div>
                <div class="text-2xl font-bold text-blue-900">{{ $technicalDebt['info'] }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-700">Debt Score</div>
                <div class="text-2xl font-bold text-gray-900">{{ $technicalDebt['technical_debt_score'] }}</div>
            </div>
        </div>
    </div>

    <!-- Repository Health -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Repository Health</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($repositoryHealth as $repo)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $repo['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $repo['health']['review_count'] }} reviews</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-500">
                            {{ $repo['health']['overall'] }}/100
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-bold
                            @if($repo['health']['grade'] === 'A' || $repo['health']['grade'] === 'B') bg-green-100 text-green-800
                            @elseif($repo['health']['grade'] === 'C') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            Grade {{ $repo['health']['grade'] }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">No repositories.</div>
            @endforelse
        </div>
    </div>

    <!-- Common Problems -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Common Problems</h2>
        <div class="space-y-2">
            @forelse($commonProblems as $problem)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <div>
                        <span class="text-xs px-2 py-0.5 bg-white rounded-full text-gray-600 border">{{ $problem['category'] }}</span>
                        <span class="ml-2 text-sm text-gray-900">{{ $problem['title'] }}</span>
                    </div>
                    <span class="text-sm font-bold text-gray-700">{{ $problem['count'] }}×</span>
                </div>
            @empty
                <p class="text-gray-500">No problems detected yet.</p>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quality Trend
    const qualityData = @json($qualityTrend);
    const qualityCtx = document.getElementById('qualityTrendChart');
    if (qualityCtx && qualityData.length > 0) {
        new Chart(qualityCtx, {
            type: 'line',
            data: {
                labels: qualityData.map(d => d.date),
                datasets: [{
                    label: 'Avg Score',
                    data: qualityData.map(d => d.avg_score),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: { responsive: true, scales: { y: { min: 0, max: 100 } } }
        });
    }

    // Security Trend
    const securityData = @json($securityTrend);
    const securityCtx = document.getElementById('securityTrendChart');
    if (securityCtx && securityData.length > 0) {
        new Chart(securityCtx, {
            type: 'line',
            data: {
                labels: securityData.map(d => d.date),
                datasets: [{
                    label: 'Security Score',
                    data: securityData.map(d => d.avg_security_score),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.1)',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: { responsive: true, scales: { y: { min: 0, max: 100 } } }
        });
    }
});
</script>
@endsection
