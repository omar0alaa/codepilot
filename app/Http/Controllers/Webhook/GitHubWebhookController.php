<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use App\Models\Repository;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify signature
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        
        if (!$this->verifySignature($signature, $payload)) {
            Log::warning('GitHub webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $eventType = $request->header('X-GitHub-Event');
        $deliveryId = $request->header('X-GitHub-Delivery-ID');
        $action = $request->input('action');

        // Find repository
        $githubRepoId = $request->input('repository.id');
        $repository = Repository::where('github_id', $githubRepoId)->first();

        if (!$repository || !$repository->is_enabled) {
            return response()->json(['message' => 'Repository not found or disabled'], 200);
        }

        // Store webhook event
        $webhookEvent = WebhookEvent::create([
            'event_id' => $deliveryId,
            'event_type' => $eventType,
            'action' => $action,
            'repository_id' => $repository?->id,
            'payload' => $request->all(),
            'status' => 'received',
        ]);

        // Dispatch to queue immediately (return 200 to GitHub)
        ProcessWebhookJob::dispatch($webhookEvent);

        return response()->json(['message' => 'Webhook received'], 200);
    }

    private function verifySignature(?string $signature, string $payload): bool
    {
        if (!$signature) {
            return false;
        }

        $secret = config('services.github.webhook_secret');
        $computedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($signature, $computedSignature);
    }
}
