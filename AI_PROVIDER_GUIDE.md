# AI Provider Configuration Guide — CodePilot AI

## Overview

CodePilot AI uses a **Strategy Pattern** to support multiple AI providers. This allows you to switch providers without changing any application code — just update the configuration.

## Architecture

```
              ┌─────────────────────┐
              │  AiProviderFactory  │
              │   (creates provider │
              │    from config)     │
              └─────────┬───────────┘
                        │
                        ▼
              ┌─────────────────────┐
              │ AiProviderInterface │
              │  (contract)         │
              └─────────┬───────────┘
                        │
          ┌─────────────┼─────────────┐
          │             │             │
          ▼             ▼             ▼
    ┌─────────┐  ┌─────────┐  ┌─────────┐
    │  Groq   │  │ OpenAI  │  │ Claude  │ ...
    │Provider │  │Provider │  │Provider │
    └─────────┘  └─────────┘  └─────────┘
```

## Supported Providers

| Provider | Status | Models |
|----------|--------|--------|
| **Groq** | ✅ Implemented | llama3-70b-8192, llama3-8b-8192, mixtral-8x7b-32768, gemma-7b-it |
| OpenAI | 📋 Planned | gpt-4-turbo, gpt-4, gpt-3.5-turbo |
| Claude (Anthropic) | 📋 Planned | claude-3-opus, claude-3-sonnet, claude-3-haiku |
| Gemini (Google) | 📋 Planned | gemini-1.5-pro, gemini-1.5-flash |
| OpenRouter | 📋 Planned | Multiple models via OpenRouter |
| Ollama | 📋 Planned | Local models (llama3, mistral, etc.) |

## Configuration

### 1. Set Default Provider

In `.env`:
```env
AI_PROVIDER=groq
```

### 2. Configure Provider Credentials

```env
# Groq
GROQ_API_KEY=your_groq_api_key
GROQ_MODEL=llama3-70b-8192

# OpenAI (when implemented)
OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-4-turbo

# Anthropic (when implemented)
ANTHROPIC_API_KEY=your_anthropic_key
ANTHROPIC_MODEL=claude-3-sonnet-20240229
```

### 3. Config File

The main configuration is in `config/ai.php`:

```php
'default' => env('AI_PROVIDER', 'groq'),

'providers' => [
    'groq' => [
        'class' => \App\Services\Ai\GroqProvider::class,
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama3-70b-8192'),
        'timeout' => 120,
    ],
    // ...
],
```

## Adding a New Provider

### 1. Create the Provider Class

```php
<?php

namespace App\Services\Ai;

use App\Contracts\Ai\AiProviderInterface;

class MyNewProvider implements AiProviderInterface
{
    public function analyze(array $context, array $promptTemplate): array
    {
        // Implementation
    }

    public function chat(string $prompt, ?string $systemMessage = null): string
    {
        // Implementation
    }

    public function getName(): string { return 'my-provider'; }
    public function getModel(): string { return $this->model; }
    public function getConfigRequirements(): array { return [...]; }
    public function validateConfig(array $config): bool { return true; }
    public function getAvailableModels(): array { return [...]; }
}
```

### 2. Register in Config

```php
// config/ai.php
'providers' => [
    'my-provider' => [
        'class' => \App\Services\Ai\MyNewProvider::class,
        'api_key' => env('MY_PROVIDER_API_KEY'),
        'model' => env('MY_PROVIDER_MODEL', 'default-model'),
    ],
],
```

### 3. Register in Factory

```php
// app/Services/Ai/AiProviderFactory.php
protected array $providers = [
    'groq' => \App\Services\Ai\GroqProvider::class,
    'my-provider' => \App\Services\Ai\MyNewProvider::class,
];
```

That's it! No other code changes needed.

## Switching Providers

To switch providers at runtime:

```bash
# In .env
AI_PROVIDER=openai
```

Or programmatically:

```php
$provider = $factory->create('openai', $config);
```

## Prompt Templates

The `PromptTemplateBuilder` constructs structured prompts for each review. Prompts request JSON output with standardized fields:

- `overall_score` (0-100)
- `category_scores` (security, performance, maintainability, readability, architecture)
- `issues` array with file, line, title, severity, description, suggestion, confidence
- `suggestions` array with type, description, priority
- `summary` text

## Context Window Management

Large diffs are automatically truncated to 12,000 characters to fit within model token limits. For extremely large PRs, the review focuses on the most significant changes first.
