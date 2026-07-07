<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PullRequest;
use App\Models\Review;

class PullRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PullRequest::with(['repository', 'reviews'])
            ->whereIn('repository_id', $request->user()->repositories()->pluck('id'));

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('head_branch', 'like', "%{$search}%");
        }

        if ($state = $request->get('state')) {
            $query->where('state', $state);
        }

        $pullRequests = $query->orderBy('github_created_at', 'desc')->paginate(20);

        return view('pull-requests.index', compact('pullRequests'));
    }

    public function show(PullRequest $pullRequest)
    {
        $this->authorize('view', $pullRequest->repository);

        $reviews = $pullRequest->reviews()->orderBy('created_at', 'desc')->get();

        return view('pull-requests.show', compact('pullRequest', 'reviews'));
    }

    public function reReview(Request $request, PullRequest $pullRequest)
    {
        $this->authorize('view', $pullRequest->repository);

        $review = Review::create([
            'pull_request_id' => $pullRequest->id,
            'user_id' => $request->user()->id,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        \App\Jobs\PerformAiReviewJob::dispatch($review->id);

        return redirect()->route('pull-requests.show', $pullRequest)
            ->with('success', 'Review queued for processing');
    }
}
