<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repository;
use App\Models\Review;
use App\Models\PullRequest;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'repositories' => $user->repositories()->count(),
            'pull_requests' => PullRequest::whereIn('repository_id', $user->repositories()->pluck('id'))->count(),
            'reviews' => Review::whereIn('pull_request_id', function ($query) use ($user) {
                $query->select('id')
                    ->from('pull_requests')
                    ->whereIn('repository_id', $user->repositories()->pluck('id'));
            })->count(),
            'avg_score' => Review::whereIn('pull_request_id', function ($query) use ($user) {
                $query->select('id')
                    ->from('pull_requests')
                    ->whereIn('repository_id', $user->repositories()->pluck('id'));
            })->where('status', 'completed')->avg('overall_score') ?? 0,
        ];

        $recentReviews = Review::with(['pullRequest.repository'])
            ->whereIn('pull_request_id', function ($query) use ($user) {
                $query->select('id')
                    ->from('pull_requests')
                    ->whereIn('repository_id', $user->repositories()->pluck('id'));
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $repositories = $user->repositories()->orderBy('updated_at', 'desc')->limit(5)->get();

        return view('dashboard', compact('stats', 'recentReviews', 'repositories'));
    }
}
