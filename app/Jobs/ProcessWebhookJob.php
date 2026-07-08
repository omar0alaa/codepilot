<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\WebhookEvent;
use App\Models\Review;
use App\Services\Webhook\WebhookProcessingService;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $timeout = 300;
    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public int $webhookEventId
    ) {}

    public function handle(WebhookProcessingService $processingService): void
    {
        $event = WebhookEvent::find($this->webhookEventId);
        if (!$event) {
            Log::warning('ProcessWebhookJob: Event not found', ['event_id' => $this->webhookEventId]);
            return;
        }

        // Skip if already processed (idempotency guard)
        if ($event->status === 'processed') {
            return;
        }

        $event->update(['status' => 'processing']);

        try {
            $eventType = $event->event_type;
            $action = $event->action;
            $payload = is_array($event->payload) ? $event->payload : json_decode($event->payload, true);

            // Handle pull_request events
            if ($eventType === 'pull_request' && in_array($action, ['opened', 'synchronize', 'reopened'])) {
                $this->triggerReview($event);
            }

            // Handle installation events
            if ($eventType === 'installation') {
                $this->handleInstallationEvent($event, $action, $payload);
            }

            $event->update(['status' => 'processed']);

            Log::info('ProcessWebhookJob: Event processed', [
                'event_id' => $event->id,
                'event_type' => $eventType,
                'action' => $action,
            ]);

        } catch (\Exception $e) {
            $event->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            Log::error('ProcessWebhookJob: Processing failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Trigger AI review for a pull request event
     */
    private function triggerReview(WebhookEvent $event): void
    {
        $pullRequest = $event->pullRequest;
        
        if (!$pullRequest) {
            Log::warning('ProcessWebhookJob: No pull request linked', ['event_id' => $event->id]);
            return;
        }

        $repository = $pullRequest->repository;

        // Create a new review record
        $review = Review::create([
            'pull_request_id' => $pullRequest->id,
            'user_id' => $repository->user_id,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        // Dispatch the AI review job
        PerformAiReviewJob::dispatch($review->id);

        Log::info('ProcessWebhookJob: Review triggered', [
            'review_id' => $review->id,
            'pr_number' => $pullRequest->number,
            'repository' => $repository->full_name,
        ]);
    }

    /**
     * Handle GitHub App installation events
     */
    private function handleInstallationEvent(WebhookEvent $event, string $action, array $payload): void
    {
        Log::info('ProcessWebhookJob: Installation event', [
            'action' => $action,
            'installation_id' => $payload['installation']['id'] ?? null,
        ]);

        // TODO: Handle 'created' (new installation) or 'deleted' (uninstallation)
        // For 'created': sync repositories
        // For 'deleted': disable all repositories for this installation
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessWebhookJob: Job failed permanently', [
            'event_id' => $this->webhookEventId,
            'error' => $exception->getMessage(),
        ]);

        $event = WebhookEvent::find($this->webhookEventId);
        if ($event) {
            $event->update([
                'status' => 'failed',
                'error_message' => 'Job failed after all retries: ' . $exception->getMessage(),
            ]);
        }
    }
}
