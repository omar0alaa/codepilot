<?php

namespace App\Services\Webhook;

use App\Models\WebhookEvent;
use App\Models\Repository;
use App\Models\PullRequest;
use Illuminate\Support\Facades\Log;

class WebhookProcessingService
{
    /**
     * Supported PR actions that trigger a review
     */
    private const REVIEWABLE_ACTIONS = ['opened', 'synchronize', 'reopened'];

    public function __construct(
        private WebhookSignatureService $signatureService
    ) {}

    /**
     * Parse and store a webhook event, return whether it should be queued
     */
    public function processEvent(array $payload, string $eventType, string $deliveryId): ?WebhookEvent
    {
        // Check for duplicate delivery
        $existing = WebhookEvent::where('event_id', $deliveryId)->first();
        if ($existing) {
            Log::info('GitHub webhook: Duplicate delivery ignored', ['delivery_id' => $deliveryId]);
            return null;
        }

        // Find repository by GitHub ID
        $githubRepoId = $payload['repository']['id'] ?? null;
        $repository = Repository::where('github_id', $githubRepoId)->first();

        if (!$repository || !$repository->is_enabled) {
            Log::info('GitHub webhook: Repository not found or disabled', [
                'github_repo_id' => $githubRepoId,
            ]);
            return null;
        }

        $action = $payload['action'] ?? 'unknown';

        // Store the webhook event
        $webhookEvent = WebhookEvent::create([
            'event_id' => $deliveryId,
            'event_type' => $eventType,
            'action' => $action,
            'repository_id' => $repository->id,
            'payload' => $payload,
            'status' => 'received',
        ]);

        // Check if this is a reviewable PR event
        if ($eventType === 'pull_request' && in_array($action, self::REVIEWABLE_ACTIONS)) {
            $this->linkPullRequest($webhookEvent, $payload);
        }

        return $webhookEvent;
    }

    /**
     * Link a webhook event to its pull request
     */
    private function linkPullRequest(WebhookEvent $event, array $payload): void
    {
        $prData = $payload['pull_request'] ?? null;
        
        if (!$prData) {
            return;
        }

        $repository = $event->repository;
        
        $pullRequest = PullRequest::updateOrCreate(
            ['github_pr_id' => $prData['id']],
            [
                'repository_id' => $repository->id,
                'user_id' => $repository->user_id,
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

        $event->update(['pull_request_id' => $pullRequest->id]);
    }
}
