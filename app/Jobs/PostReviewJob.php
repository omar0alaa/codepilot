<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Review;
use App\Services\GitHub\GitHubApiService;
use App\Services\GitHub\GitHubTokenService;
use App\Services\GitHub\GitHubCheckService;

class PostReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $timeout = 120;
    public $tries = 3;
    public $backoff = [5, 15, 30];

    public function __construct(
        public int $reviewId
    ) {}

    public function handle(
        GitHubApiService $githubApi,
        GitHubTokenService $tokenService,
        GitHubCheckService $checkService
    ): void {
        $review = Review::with(['pullRequest.repository.user'])->find($this->reviewId);
        
        if (!$review || $review->status !== 'completed') {
            Log::warning('PostReviewJob: Review not found or not completed', [
                'review_id' => $this->reviewId,
                'status' => $review?->status,
            ]);
            return;
        }

        $pullRequest = $review->pullRequest;
        $repository = $pullRequest->repository;
        $user = $repository->user;

        $token = $tokenService->getToken($user);
        if (!$token) {
            Log::error('PostReviewJob: No GitHub token for user', ['user_id' => $user->id]);
            return;
        }

        // Build review body for GitHub
        $reviewBody = $this->buildReviewBody($review);

        // Post review to GitHub
        $result = $githubApi->postReview(
            $token,
            $repository->github_id,
            $pullRequest->number,
            $reviewBody
        );

        Log::info('PostReviewJob: Review posted to GitHub', [
            'review_id' => $review->id,
            'pr_number' => $pullRequest->number,
            'github_review_id' => $result['id'] ?? null,
        ]);

        // Also create a GitHub Check run with annotations
        try {
            $checkData = [
                'overall_score' => $review->overall_score,
                'category_scores' => $review->category_scores,
                'issues' => $review->issues,
                'summary' => $review->summary,
            ];

            // Get head SHA from PR (stored or from webhook payload)
            $headSha = $pullRequest->github_url; // Will need actual SHA
            
            $checkService->createReviewCheck($token, $repository->github_id, $headSha, $checkData);
            
            Log::info('PostReviewJob: Check run created', ['review_id' => $review->id]);
        } catch (\Exception $e) {
            Log::warning('PostReviewJob: Check run failed (non-blocking)', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build the review payload for GitHub API
     */
    private function buildReviewBody(Review $review): array
    {
        $issues = $review->issues ?? [];
        $suggestions = $review->suggestions ?? [];
        
        $body = "## CodePilot AI Review\n\n";
        $body .= "**Overall Score: {$review->overall_score}/100**\n\n";

        // Category scores
        if ($review->category_scores) {
            $body .= "### Category Scores\n\n";
            $body .= "| Category | Score |\n|----------|-------|\n";
            foreach ($review->category_scores as $category => $score) {
                $body .= "| " . ucfirst($category) . " | {$score}/100 |\n";
            }
            $body .= "\n";
        }

        // Summary
        if ($review->summary) {
            $body .= "### Summary\n\n{$review->summary}\n\n";
        }

        // Issues
        if (count($issues) > 0) {
            $body .= "### Issues Found (" . count($issues) . ")\n\n";
            foreach ($issues as $index => $issue) {
                $severity = $issue['severity'] ?? 'info';
                $emoji = match($severity) {
                    'critical' => '🔴',
                    'warning' => '🟡',
                    'info' => '🔵',
                    default => '⚪',
                };
                $body .= "{$emoji} **" . ($issue['title'] ?? 'Issue') . "** [{$severity}]\n";
                if (isset($issue['description'])) {
                    $body .= "   - {$issue['description']}\n";
                }
                if (isset($issue['suggestion'])) {
                    $body .= "   - **Suggestion:** {$issue['suggestion']}\n";
                }
                $body .= "\n";
            }
        } else {
            $body .= "✅ No significant issues detected.\n\n";
        }

        // Determine event (approve, request_changes, comment)
        $event = 'comment';
        if ($review->overall_score !== null) {
            if ($review->overall_score >= 80) {
                $event = 'APPROVE';
            } elseif ($review->overall_score < 50) {
                $event = 'REQUEST_CHANGES';
            }
        }

        return [
            'body' => $body,
            'event' => $event,
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('PostReviewJob: Failed permanently', [
            'review_id' => $this->reviewId,
            'error' => $exception->getMessage(),
        ]);

        $review = Review::find($this->reviewId);
        if ($review) {
            $review->update(['status' => 'failed']);
        }
    }
}
