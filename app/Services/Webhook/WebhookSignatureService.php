<?php

namespace App\Services\Webhook;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookSignatureService
{
    /**
     * Verify the GitHub webhook signature
     * 
     * GitHub sends X-Hub-Signature-256 header which is:
     * 'sha256=' + HMAC-SHA256 of the raw payload using the webhook secret
     */
    public function verify(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        
        if (!$signature) {
            Log::warning('GitHub webhook: Missing X-Hub-Signature-256 header');
            return false;
        }

        $secret = config('services.github.webhook_secret');
        
        if (!$secret) {
            Log::error('GitHub webhook: No webhook secret configured');
            return false;
        }

        $computedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        if (!hash_equals($signature, $computedSignature)) {
            Log::warning('GitHub webhook: Signature verification failed', [
                'received' => substr($signature, 0, 20) . '...',
                'computed' => substr($computedSignature, 0, 20) . '...',
            ]);
            return false;
        }

        return true;
    }

    /**
     * Verify raw payload and signature (for testing or manual verification)
     */
    public function verifyRaw(string $payload, string $signature): bool
    {
        $secret = config('services.github.webhook_secret');
        
        if (!$secret || !$signature) {
            return false;
        }

        $computedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($signature, $computedSignature);
    }
}
