<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Http;

class GitHubApiService
{
    private string $baseUrl = 'https://api.github.com';

    /**
     * Get authenticated HTTP client
     */
    private function client(string $token): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'token ' . $token,
            'Accept' => 'application/vnd.github.v3+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->timeout(30);
    }

    /**
     * Get installation repositories
     */
    public function getInstallationRepositories(string $installationId): array
    {
        $response = $this->client('')->get(
            $this->baseUrl . "/app/installations/{$installationId}/repositories"
        );

        return $response->json();
    }

    /**
     * Get pull request diff
     */
    public function getPullRequestDiff(string $token, int $repoId, int $prNumber): string
    {
        $response = $this->client($token)
            ->withHeaders(['Accept' => 'application/vnd.github.v3.diff'])
            ->get($this->baseUrl . "/repositories/{$repoId}/pulls/{$prNumber}");

        return $response->body();
    }

    /**
     * Post review comment to PR
     */
    public function postReview(string $token, int $repoId, int $prNumber, array $review): array
    {
        $response = $this->client($token)
            ->post($this->baseUrl . "/repositories/{$repoId}/pulls/{$prNumber}/reviews", $review);

        return $response->json();
    }

    /**
     * Create a check run (GitHub Checks API)
     */
    public function createCheckRun(string $token, int $repoId, array $check): array
    {
        $response = $this->client($token)
            ->post($this->baseUrl . "/repositories/{$repoId}/check-runs", $check);

        return $response->json();
    }

    /**
     * Get repository details
     */
    public function getRepository(string $token, string $owner, string $repo): array
    {
        $response = $this->client($token)
            ->get($this->baseUrl . "/repos/{$owner}/{$repo}");

        return $response->json();
    }

    /**
     * List pull requests for a repository
     */
    public function listPullRequests(string $token, string $owner, string $repo, string $state = 'open'): array
    {
        $response = $this->client($token)
            ->get($this->baseUrl . "/repos/{$owner}/{$repo}/pulls", [
                'state' => $state,
                'per_page' => 50,
            ]);

        return $response->json();
    }
}
