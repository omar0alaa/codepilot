<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GitHub Checks API Service
 * 
 * Creates check runs on PRs to show review status as a GitHub check
 */
class GitHubCheckService
{
    private string $baseUrl = 'https://api.github.com';

    public function __construct(
        private GitHubApiService $githubApi
    ) {}

    /**
     * Create a check run for a pull request review
     */
    public function createReviewCheck(
        string $token,
        int $repoId,
        string $headSha,
        array $reviewData
    ): array {
        $overallScore = $reviewData['overall_score'] ?? 0;
        $issues = $reviewData['issues'] ?? [];
        
        $criticalCount = count(array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'critical'));
        $warningCount = count(array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'warning'));
        $infoCount = count(array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'info'));

        $conclusion = $overallScore >= 50 ? 'success' : 'failure';
        $summary = "Score: {$overallScore}/100 | Issues: {$criticalCount} critical, {$warningCount} warnings, {$infoCount} info";

        $text = "## CodePilot AI Review\n\n";
        $text .= "**Overall Score:** {$overallScore}/100\n\n";

        if (!empty($reviewData['category_scores'])) {
            $text .= "### Category Scores\n\n";
            $text .= "| Category | Score |\n|----------|-------|\n";
            foreach ($reviewData['category_scores'] as $category => $score) {
                $text .= "| " . ucfirst($category) . " | {$score}/100 |\n";
            }
            $text .= "\n";
        }

        if (!empty($reviewData['summary'])) {
            $text .= "### Summary\n\n{$reviewData['summary']}\n\n";
        }

        $annotations = $this->buildAnnotations($issues);

        $payload = [
            'name' => 'CodePilot AI Review',
            'head_sha' => $headSha,
            'status' => 'completed',
            'conclusion' => $conclusion,
            'output' => [
                'title' => "CodePilot AI — Score {$overallScore}/100",
                'summary' => $summary,
                'text' => $text,
                'annotations' => $annotations,
            ],
            'actions' => [],
        ];

        return $this->githubApi->createCheckRun($token, $repoId, $payload);
    }

    /**
     * Build GitHub annotations from review issues
     */
    private function buildAnnotations(array $issues): array
    {
        $annotations = [];
        
        foreach (array_slice($issues, 0, 50) as $issue) {
            $annotations[] = [
                'path' => $issue['file'] ?? 'unknown',
                'start_line' => (int) ($issue['line'] ?? 1),
                'end_line' => (int) ($issue['line'] ?? 1),
                'annotation_level' => match($issue['severity'] ?? 'info') {
                    'critical' => 'failure',
                    'warning' => 'warning',
                    default => 'notice',
                },
                'message' => $issue['title'] ?? 'Issue detected',
                'raw_details' => $issue['description'] ?? '',
            ];
        }

        return $annotations;
    }

    /**
     * Create an in-progress check run (when review starts)
     */
    public function createPendingCheck(string $token, int $repoId, string $headSha): array
    {
        $payload = [
            'name' => 'CodePilot AI Review',
            'head_sha' => $headSha,
            'status' => 'in_progress',
            'started_at' => now()->toISOString(),
        ];

        return $this->githubApi->createCheckRun($token, $repoId, $payload);
    }
}
