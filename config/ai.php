<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This is the default AI provider that will be used for code reviews.
    | You can change this to any provider defined in the 'providers' array.
    |
    */

    'default' => env('DEFAULT_AI_PROVIDER', 'groq'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Here you may define all the AI providers that your application uses.
    | Each provider has a driver (the implementation class) and configuration.
    |
    */

    'providers' => [
        'groq' => [
            'driver' => \App\Services\Ai\GroqProvider::class,
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama3-70b-8192'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        ],

        'openai' => [
            'driver' => \App\Services\Ai\OpenAiProvider::class,
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'organization' => env('OPENAI_ORGANIZATION'),
        ],

        'claude' => [
            'driver' => \App\Services\Ai\ClaudeProvider::class,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
        ],

        'gemini' => [
            'driver' => \App\Services\Ai\GeminiProvider::class,
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
        ],

        'ollama' => [
            'driver' => \App\Services\Ai\OllamaProvider::class,
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama3'),
        ],

        'openrouter' => [
            'driver' => \App\Services\Ai\OpenRouterProvider::class,
            'api_key' => env('OPENROUTER_API_KEY'),
            'model' => env('OPENROUTER_MODEL', 'openai/gpt-4-turbo-preview'),
            'site_url' => env('OPENROUTER_SITE_URL', 'https://codepilot.ai'),
            'site_name' => env('OPENROUTER_SITE_NAME', 'CodePilot AI'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Review Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for how code reviews are performed.
    |
    */

    'review' => [
        'max_files_per_request' => env('AI_MAX_FILES', 50),
        'max_diff_size_kb' => env('AI_MAX_DIFF_KB', 100),
        'timeout_seconds' => env('AI_TIMEOUT', 120),
        'retry_attempts' => env('AI_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Templates
    |--------------------------------------------------------------------------
    |
    | Default prompt templates for different review categories.
    |
    */

    'prompt_templates' => [
        'default' => [
            'system_prompt' => 'You are a senior software engineer conducting a code review. Analyze the provided code changes and identify issues related to security, performance, maintainability, and best practices.',
            'user_prompt' => 'Please review the following code changes:

**PR Metadata:**
{{PR_METADATA}}

**Changed Files:**
{{FILES}}

**Diff:**
{{DIFF}}

Provide your analysis in JSON format with the following structure:
{
  "overall_score": <0-100>,
  "category_scores": {"security": <0-100>, "performance": <0-100>, ...},
  "issues": [{"file": "...", "line": ..., "severity": "...", "description": "..."}],
  "suggestions": [{"file": "...", "suggestion": "...", "example_code": "..."}],
  "summary": "..."
}',
            'temperature' => 0.3,
            'max_tokens' => 4096,
        ],
    ],
];
