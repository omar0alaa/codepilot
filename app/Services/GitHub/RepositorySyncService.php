<?php

namespace App\Services\GitHub;

use App\Models\User;
use App\Models\Repository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RepositorySyncService
{
    public function __construct(
        private GitHubApiService $githubApi,
        private GitHubTokenService $tokenService
    ) {}

    /**
     * Sync all repositories for a user from GitHub
     */
    public function syncUserRepositories(User $user): array
    {
        $token = $this->tokenService->getToken($user);
        
        if (!$token) {
            return ['synced' => 0, 'error' => 'No GitHub token found'];
        }

        $repos = $this->githubApi->listRepositories($token, $user->github_username);
        
        $synced = 0;
        foreach ($repos as $repoData) {
            Repository::updateOrCreate(
                ['github_id' => $repoData['id']],
                [
                    'user_id' => $user->id,
                    'name' => $repoData['name'],
                    'full_name' => $repoData['full_name'],
                    'description' => $repoData['description'] ?? null,
                    'is_private' => $repoData['private'],
                    'default_branch' => $repoData['default_branch'] ?? 'main',
                    'is_enabled' => true,
                    'github_created_at' => $repoData['created_at'] ?? null,
                ]
            );
            $synced++;
        }

        Log::info("Synced {$synced} repositories for user {$user->id}");

        return ['synced' => $synced];
    }

    /**
     * Sync a single repository's metadata
     */
    public function syncRepository(Repository $repository): void
    {
        $token = $this->tokenService->getToken($repository->user);
        
        if (!$token) {
            return;
        }

        $parts = explode('/', $repository->full_name);
        $owner = $parts[0] ?? null;
        $name = $parts[1] ?? null;

        if (!$owner || !$name) {
            return;
        }

        $repoData = $this->githubApi->getRepository($token, $owner, $name);

        $repository->update([
            'description' => $repoData['description'] ?? null,
            'is_private' => $repoData['private'] ?? false,
            'default_branch' => $repoData['default_branch'] ?? 'main',
        ]);
    }

    /**
     * Enable review for a repository
     */
    public function enableReview(Repository $repository): void
    {
        // Setup webhook on the repository
        $token = $this->tokenService->getToken($repository->user);
        
        if (!$token) {
            return;
        }

        $parts = explode('/', $repository->full_name);
        $owner = $parts[0] ?? null;
        $name = $parts[1] ?? null;

        if (!$owner || !$name) {
            return;
        }

        // Create webhook on the repository
        Http::withToken($token)
            ->post("https://api.github.com/repos/{$owner}/{$name}/hooks", [
                'name' => 'web',
                'active' => true,
                'events' => ['pull_request'],
                'config' => [
                    'url' => config('app.url') . '/github/webhook',
                    'content_type' => 'json',
                    'secret' => config('services.github.webhook_secret'),
                ],
            ]);

        $repository->update(['is_enabled' => true]);
    }

    /**
     * Disable review for a repository
     */
    public function disableReview(Repository $repository): void
    {
        $repository->update(['is_enabled' => false]);
    }
}
