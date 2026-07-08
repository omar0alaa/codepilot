<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\Review;
use App\Services\Ai\ReviewAnalyzerService;
use App\Services\GitHub\GitHubApiService;
use App\Services\GitHub\GitHubTokenService;

class PerformAiReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $timeout = 300;
    public $tries = 2;
    public $backoff = [10, 30];

    public function __construct(
        public int $reviewId
    ) {}

    public function handle(
        ReviewAnalyzerService $analyzer,
        GitHubApiService $githubApi,
        GitHubTokenService $tokenService
    ): void {
        $review = Review::with(['pullRequest.repository.user'])->find($this->reviewId);
        
        if (!$review) {
            Log::warning('PerformAiReviewJob: Review not found', ['review_id' => $this->reviewId]);
            return;
        }

        try {
            $pullRequest = $review->pullRequest;
            $repository = $pullRequest->repository;
            $user = $repository->user;

            // Fetch the PR diff from GitHub
            $token = $tokenService->getToken($user);
            $diff = '';
            
            if ($token) {
                $diff = $githubApi->getPullRequestDiff(
                    $token,
                    $repository->github_id,
                    $pullRequest->number
                );
            }

            // Build PR context for the AI
            $prContext = [
                'repository' => [
                    'full_name' => $repository->full_name,
                    'default_branch' => $repository->default_branch,
                ],
                'pr_number' => $pullRequest->number,
                'pr_title' => $pullRequest->title,
                'base_branch' => $pullRequest->base_branch,
                'head_branch' => $pullRequest->head_branch,
            ];

            // Perform AI analysis
            $result = $analyzer->analyze($diff, $prContext);

            // Update review with results
            $review->update([
                'status' => 'completed',
                'overall_score' => $result['overall_score'],
                'category_scores' => $result['category_scores'],
                'issues' => $result['issues'],
                'suggestions' => $result['suggestions'],
                'summary' => $result['summary'],
                'ai_provider' => $result['provider'],
                'ai_model' => $result['model'],
                'completed_at' => now(),
            ]);

            Log::info('PerformAiReviewJob: Review completed', [
                'review_id' => $review->id,
                'score' => $result['overall_score'],
                'issues' => count($result['issues']),
            ]);

            // Dispatch job to post review comments back to GitHub PR
            PostReviewJob::dispatch($review->id);

        } catch (\Exception $e) {
            $review->update(['status' => 'failed']);
            
            Log::error('PerformAiReviewJob: Review failed', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('PerformAiReviewJob: Job failed permanently', [
            'review_id' => $this->reviewId,
            'error' => $exception->getMessage(),
        ]);

        $review = Review::find($this->reviewId);
        if ($review) {
            $review->update(['status' => 'failed']);
        }
    }
}
