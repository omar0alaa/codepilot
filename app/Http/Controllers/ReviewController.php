<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['pullRequest.repository'])
            ->whereIn('pull_request_id', function ($q) use ($request) {
                $q->select('id')->from('pull_requests')
                    ->whereIn('repository_id', $request->user()->repositories()->pluck('id'));
            });

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        $this->authorize('view', $review->pullRequest->repository);

        return view('reviews.show', compact('review'));
    }
}
