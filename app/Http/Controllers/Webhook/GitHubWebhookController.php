<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Webhook\WebhookSignatureService;
use App\Services\Webhook\WebhookProcessingService;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class GitHubWebhookController extends Controller
{
    public function __construct(
        private WebhookSignatureService $signatureService,
        private WebhookProcessingService $processingService
    ) {}

    /**
     * Handle incoming GitHub webhook
     * 
     * Flow: Receive → Verify Signature → Store Event → Dispatch Queue Job → Return 200
     */
    public function handle(Request $request)
    {
        // Rate limit: 100 webhooks per minute per IP
        $key = 'webhook:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 100)) {
            Log::warning('GitHub webhook: Rate limit exceeded', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Rate limit exceeded'], 429);
        }
        RateLimiter::hit($key, 60);

        // Verify GitHub signature (HMAC SHA256)
        if (!$this->signatureService->verify($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $eventType = $request->header('X-GitHub-Event');
        $deliveryId = $request->header('X-GitHub-Delivery') ?? uniqid('delivery_');

        // Only process relevant events
        if (!in_array($eventType, ['pull_request', 'ping', 'installation', 'installation_repositories'])) {
            return response()->json(['message' => 'Event type ignored'], 200);
        }

        // Handle ping event (sent when webhook is first created)
        if ($eventType === 'ping') {
            return response()->json(['message' => 'Pong! Webhook is configured correctly'], 200);
        }

        // Process and store the event
        $webhookEvent = $this->processingService->processEvent(
            $request->all(),
            $eventType,
            $deliveryId
        );

        if (!$webhookEvent) {
            return response()->json(['message' => 'Event ignored (duplicate or unknown repo)'], 200);
        }

        // Dispatch to queue immediately — return 200 to GitHub right away
        ProcessWebhookJob::dispatch($webhookEvent->id);

        Log::info('GitHub webhook: Received and queued', [
            'event_id' => $deliveryId,
            'event_type' => $eventType,
            'action' => $webhookEvent->action,
        ]);

        return response()->json([
            'message' => 'Webhook received and queued for processing',
            'event_id' => $webhookEvent->event_id,
            'status' => 'queued',
        ], 200);
    }
}
