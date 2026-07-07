<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\Review;
use App\Models\PullRequest;
use App\Services\Ai\AiProviderFactory;

class PerformAiReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $timeout = 300;
    public $tries = 2;

    public function __construct(
        public int $reviewId
    ) {}

    public function handle(AiProviderFactory $factory): void
    {
        $review = Review::find($this->reviewId);
        if (!$review) {
            return;
        }

        try {
            $pullRequest = $review->pullRequest;
            $repository = $pullRequest->repository;

            // Get the default AI provider
            $aiProvider = $factory->create(
                config('ai.default', 'groq'),
                config('ai.providers.' . config('ai.default'), [])
            );

            // TODO: Fetch actual diff from GitHub API using repository->user->github_token
            // For now, using placeholder context
            $context = [
                'files' => [], // TODO: extract from PR
                'diff' => '', // TODO: fetch from GitHub API
                'pr_metadata' => [
                    'title' => $pullRequest->title,
                    'number' => $pullRequest->number,
                    'base_branch' => $pullRequest->base_branch,
                ],
            ];

            $promptTemplate = config('ai.prompt_templates.default', []);

            // Perform AI analysis
            $result = $aiProvider->analyze($context, $promptTemplate);

            // Update review with results
            $review->update([
                'status' => 'completed',
                'overall_score' => $result['overall_score'] ?? 0,
                'category_scores' => $result['category_scores'] ?? [],
                'issues' => $result['issues'] ?? [],
                'suggestions' => $result['suggestions'] ?? [],
                'summary' => $result['summary'] ?? '',
                'ai_provider' => $result['provider'] ?? $aiProvider->getName(),
                'ai_model' => $result['model'] ?? '',
                'completed_at' => now(),
            ]);

            // TODO: Post review comments back to GitHub PR
            // PostReviewJob::dispatch($review->id);

        } catch (\Exception $e) {
            $review->update([
                'status' => 'failed',
            ]);
            Log::error('AI review failed', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
