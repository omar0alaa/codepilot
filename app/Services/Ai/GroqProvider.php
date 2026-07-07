<?php

namespace App\Services\Ai;

use App\Contracts\Ai\AiProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Groq AI Provider Implementation
 * 
 * Groq provides fast inference for open-weight models
 * API Docs: https://console.groq.com/docs/api-reference
 */
class GroqProvider implements AiProviderInterface
{
    private Client $httpClient;
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct(string $apiKey, string $model = 'llama3-70b-8192')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 120,
        ]);
    }

    /**
     * Analyze code changes using Groq API
     */
    public function analyze(array $context, array $promptTemplate): array
    {
        $prompt = $this->buildPrompt($context, $promptTemplate);

        try {
            $response = $this->httpClient->post('/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $promptTemplate['system_prompt'] ?? 'You are a senior software engineer reviewing code.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => $promptTemplate['temperature'] ?? 0.3,
                    'max_tokens' => $promptTemplate['max_tokens'] ?? 4096,
                    'response_format' => ['type' => 'json_object']
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $content = $body['choices'][0]['message']['content'] ?? '';

            return $this->parseResponse($content);

        } catch (GuzzleException $e) {
            throw new \Exception('Groq API error: ' . $e->getMessage());
        }
    }

    /**
     * Build analysis prompt from context
     */
    private function buildPrompt(array $context, array $promptTemplate): string
    {
        $files = $context['files'] ?? [];
        $diff = $context['diff'] ?? '';
        $prMetadata = $context['pr_metadata'] ?? [];

        $prompt = $promptTemplate['user_prompt'] ?? '';
        
        // Replace placeholders
        $prompt = str_replace('{{DIFF}}', $diff, $prompt);
        $prompt = str_replace('{{FILES}}', json_encode($files, JSON_PRETTY_PRINT), $prompt);
        $prompt = str_replace('{{PR_METADATA}}', json_encode($prMetadata, JSON_PRETTY_PRINT), $prompt);

        return $prompt;
    }

    /**
     * Parse AI response into structured format
     */
    private function parseResponse(string $content): array
    {
        $data = json_decode($content, true);

        return [
            'overall_score' => $data['overall_score'] ?? 0,
            'category_scores' => $data['category_scores'] ?? [],
            'issues' => $data['issues'] ?? [],
            'suggestions' => $data['suggestions'] ?? [],
            'summary' => $data['summary'] ?? '',
            'provider' => $this->getName(),
            'model' => $this->model,
        ];
    }

    public function getName(): string
    {
        return 'groq';
    }

    public function getConfigRequirements(): array
    {
        return [
            'api_key' => ['required', 'string'],
            'model' => ['required', 'string'],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['api_key']) && !empty($config['model']);
    }

    public function getAvailableModels(): array
    {
        return [
            'llama3-70b-8192' => 'Llama 3 70B (8k context)',
            'llama3-8b-8192' => 'Llama 3 8B (8k context)',
            'mixtral-8x7b-32768' => 'Mixtral 8x7B (32k context)',
            'gemma-7b-it' => 'Gemma 7B (8k context)',
        ];
    }
}
