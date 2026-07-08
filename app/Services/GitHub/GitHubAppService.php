<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubAppService
{
    private string $baseUrl = 'https://api.github.com';

    /**
     * Get a JWT for GitHub App authentication
     */
    private function getAppJwt(): string
    {
        $privateKey = config('services.github.app_private_key');
        $appId = config('services.github.app_id');

        if (!$privateKey || !$appId) {
            throw new \RuntimeException('GitHub App credentials not configured');
        }

        $now = time();
        $payload = [
            'iat' => $now - 60,
            'exp' => $now + 600,
            'iss' => $appId,
        ];

        // Use Firebase JWT or similar
        return \Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');
    }

    /**
     * Get an installation access token
     */
    public function getInstallationToken(string $installationId): string
    {
        $jwt = $this->getAppJwt();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwt,
            'Accept' => 'application/vnd.github.v3+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->post($this->baseUrl . "/app/installations/{$installationId}/access_tokens");

        if (!$response->successful()) {
            Log::error('Failed to get installation token', [
                'installation_id' => $installationId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to get installation token');
        }

        return $response->json()['token'];
    }

    /**
     * Get repositories for an installation
     */
    public function getInstallationRepos(string $installationId): array
    {
        $token = $this->getInstallationToken($installationId);

        $response = Http::withHeaders([
            'Authorization' => 'token ' . $token,
            'Accept' => 'application/vnd.github.v3+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->get($this->baseUrl . '/installation/repositories');

        return $response->json()['repositories'] ?? [];
    }

    /**
     * Revoke an installation
     */
    public function revokeInstallation(string $installationId): void
    {
        $jwt = $this->getAppJwt();

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwt,
            'Accept' => 'application/vnd.github.v3+json',
        ])->delete($this->baseUrl . "/app/installations/{$installationId}");
    }
}
