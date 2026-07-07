<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\WebhookEvent;
use App\Models\PullRequest;
use App\Models\Review;
use App\Services\Ai\AiProviderFactory;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $timeout = 300;
    public $tries = 3;

    public function __construct(
        public int $webhookEventId
    ) {}

    public function handle(): void
    {
        $event = WebhookEvent::find($this->webhookEventId);
        if (!$event) {
            return;
        }

        $event->update(['status' => 'processing']);

        try {
            $payload = $event->payload;
            $action = $event->action;

            if ($event->event_type === 'pull_request' && in_array($action, ['opened', 'synchronize', 'reopened'])) {
                $this->processPullRequestReview($event, $payload);
            }

            $event->update(['status' => 'processed']);

        } catch (\Exception $e) {
            $event->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('Webhook processing failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function processPullRequestReview(WebhookEvent $event, array $payload): void
    {
        $prData = $payload['pull_request'];
        $repo = $event->repository;

        // Find or create PullRequest
        $pullRequest = PullRequest::updateOrCreate(
            ['github_pr_id' => $prData['id']],
            [
                'repository_id' => $repo->id,
                'user_id' => $repo->user_id,
                'number' => $prData['number'],
                'title' => $prData['title'],
                'state' => $prData['state'],
                'head_branch' => $prData['head']['ref'],
                'base_branch' => $prData['base']['ref'],
                'github_url' => $prData['html_url'],
                'github_created_at' => $prData['created_at'],
                'github_updated_at' => $prData['updated_at'],
            ]
        );

        // Create Review record
        $review = Review::create([
            'pull_request_id' => $pullRequest->id,
            'user_id' => $repo->user_id,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        // Dispatch AI review job
        PerformAiReviewJob::dispatch($review->id);
    }
}
