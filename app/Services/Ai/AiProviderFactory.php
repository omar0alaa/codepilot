<?php

namespace App\Services\Ai;

use App\Contracts\Ai\AiProviderInterface;
use InvalidArgumentException;

/**
 * AI Provider Factory
 * 
 * This service allows easy swapping of AI providers.
 * Add new providers to the $providers array without changing
 * any code that uses the factory.
 */
class AiProviderFactory
{
    /**
     * Registered AI providers
     * 
     * To add a new provider:
     * 1. Create a class implementing AiProviderInterface
     * 2. Add it to this array with a unique key
     */
    private array $providers = [
        'groq' => GroqProvider::class,
        // 'openai' => OpenAiProvider::class,    // Ready for future implementation
        // 'claude' => ClaudeProvider::class,    // Ready for future implementation
        // 'gemini' => GeminiProvider::class,    // Ready for future implementation
        // 'ollama' => OllamaProvider::class,    // Ready for future implementation
    ];

    /**
     * Create an AI provider instance
     *
     * @param string $provider Provider key (groq, openai, claude, etc.)
     * @param array $config Provider-specific configuration
     * @return AiProviderInterface
     */
    public function create(string $provider, array $config = []): AiProviderInterface
    {
        if (!isset($this->providers[$provider])) {
            throw new InvalidArgumentException(
                "AI provider '{$provider}' not found. " .
                "Available providers: " . implode(', ', array_keys($this->providers))
            );
        }

        $providerClass = $this->providers[$provider];

        // For Groq, we need API key and model
        if ($provider === 'groq') {
            return new $providerClass(
                $config['api_key'] ?? env('GROQ_API_KEY'),
                $config['model'] ?? env('GROQ_MODEL', 'llama3-70b-8192')
            );
        }

        // For future providers, you can add specific constructor logic here
        // Example:
        // if ($provider === 'openai') {
        //     return new $providerClass($config['api_key'], $config['organization'] ?? null);
        // }

        // Generic instantiation (if constructor signature matches)
        return new $providerClass($config);
    }

    /**
     * Get all available provider keys
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Register a new provider dynamically
     * 
     * @param string $key Provider key
     * @param string $class Fully qualified class name
     */
    public function registerProvider(string $key, string $class): void
    {
        if (!implements_interface($class, AiProviderInterface::class)) {
            throw new InvalidArgumentException(
                "Provider class must implement AiProviderInterface"
            );
        }

        $this->providers[$key] = $class;
    }

    /**
     * Create provider from database configuration
     * 
     * @param int $providerId ID of the AI provider in database
     */
    public function createFromDatabase(int $providerId): AiProviderInterface
    {
        // TODO: Implement when we have the AI providers table
        // $providerConfig = AiProvider::findOrFail($providerId);
        // return $this->create($providerConfig->type, $providerConfig->config);
        
        throw new \Exception('Database provider configuration not yet implemented');
    }
}
