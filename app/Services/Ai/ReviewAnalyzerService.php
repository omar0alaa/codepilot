<?php

namespace App\Services\Ai;

/**
 * Review Analyzer Service
 * 
 * Orchestrates the AI review pipeline:
 * 1. Receives diff and PR context
 * 2. Builds structured prompt
 * 3. Calls AI provider
 * 4. Parses and validates response
 * 5. Returns formatted review data
 */
class ReviewAnalyzerService
{
    public function __construct(
        private AiProviderFactory $providerFactory,
        private \App\Services\Ai\Prompts\PromptTemplateBuilder $promptBuilder
    ) {}

    /**
     * Perform a complete AI review of a pull request diff
     */
    public function analyze(string $diff, array $prContext): array
    {
        // Build the review prompt
        $prompt = $this->promptBuilder->buildReviewPrompt($diff, $prContext);

        // Get the AI provider
        $provider = $this->providerFactory->create(
            config('ai.default', 'groq'),
            config('ai.providers.' . config('ai.default'), [])
        );

        // Call the AI provider
        $systemMessage = 'You are CodePilot AI, an expert code reviewer. Always respond with valid JSON.';
        $rawResponse = $provider->chat($prompt, $systemMessage);

        // Parse the JSON response
        $parsedResponse = $this->parseResponse($rawResponse);

        // Validate and normalize the results
        return $this->normalizeResults($parsedResponse, $provider->getName(), $provider->getModel());
    }

    /**
     * Parse JSON from AI response (handles markdown wrappers)
     */
    private function parseResponse(string $response): array
    {
        // Try direct JSON parse
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Try extracting JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $response, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try finding JSON object in text
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Fallback: return empty review
        return [
            'overall_score' => 0,
            'category_scores' => [],
            'issues' => [],
            'summary' => 'Failed to parse AI response',
            'suggestions' => [],
        ];
    }

    /**
     * Normalize and validate review results
     */
    private function normalizeResults(array $data, string $providerName, string $model): array
    {
        $overallScore = $this->clampScore($data['overall_score'] ?? 0);
        
        $categoryScores = [];
        foreach (($data['category_scores'] ?? []) as $category => $score) {
            $categoryScores[$category] = $this->clampScore($score);
        }

        // If no category scores, derive from overall
        if (empty($categoryScores)) {
            $categoryScores = [
                'security' => $overallScore,
                'performance' => $overallScore,
                'maintainability' => $overallScore,
                'readability' => $overallScore,
                'architecture' => $overallScore,
            ];
        }

        $issues = [];
        foreach (($data['issues'] ?? []) as $issue) {
            $issues[] = [
                'file' => $issue['file'] ?? 'unknown',
                'line' => $issue['line'] ?? 'N/A',
                'title' => $issue['title'] ?? 'Untitled Issue',
                'severity' => $this->normalizeSeverity($issue['severity'] ?? 'info'),
                'category' => $issue['category'] ?? 'general',
                'description' => $issue['description'] ?? '',
                'suggestion' => $issue['suggestion'] ?? '',
                'example_code' => $issue['example_code'] ?? '',
                'confidence' => $this->normalizeConfidence($issue['confidence'] ?? 0.5),
            ];
        }

        $suggestions = [];
        foreach (($data['suggestions'] ?? []) as $suggestion) {
            $suggestions[] = [
                'type' => $suggestion['type'] ?? 'improvement',
                'description' => $suggestion['description'] ?? '',
                'priority' => $this->normalizePriority($suggestion['priority'] ?? 'medium'),
            ];
        }

        return [
            'overall_score' => $overallScore,
            'category_scores' => $categoryScores,
            'issues' => $issues,
            'suggestions' => $suggestions,
            'summary' => $data['summary'] ?? 'No summary available.',
            'provider' => $providerName,
            'model' => $model,
        ];
    }

    /**
     * Clamp score to 0-100 range
     */
    private function clampScore($score): int
    {
        $score = (int) $score;
        return max(0, min(100, $score));
    }

    /**
     * Normalize severity value
     */
    private function normalizeSeverity(string $severity): string
    {
        $severity = strtolower($severity);
        $valid = ['critical', 'warning', 'info'];
        return in_array($severity, $valid) ? $severity : 'info';
    }

    /**
     * Normalize confidence (0.0 to 1.0)
     */
    private function normalizeConfidence($confidence): float
    {
        $confidence = (float) $confidence;
        return max(0.0, min(1.0, $confidence));
    }

    /**
     * Normalize priority
     */
    private function normalizePriority(string $priority): string
    {
        $priority = strtolower($priority);
        $valid = ['high', 'medium', 'low'];
        return in_array($priority, $valid) ? $priority : 'medium';
    }
}
