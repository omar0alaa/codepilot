<?php

namespace App\Contracts\Ai;

/**
 * AI Provider Interface (Strategy Pattern)
 * 
 * All AI providers must implement this interface.
 * This allows easy swapping of AI providers without changing the core review logic.
 */
interface AiProviderInterface
{
    /**
     * Analyze code changes and return review suggestions
     *
     * @param array $context Code context (files, diffs, metadata)
     * @param array $promptTemplate Prompt template configuration
     * @return array Review results with suggestions
     */
    public function analyze(array $context, array $promptTemplate): array;

    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Get provider configuration requirements
     */
    public function getConfigRequirements(): array;

    /**
     * Validate provider configuration
     */
    public function validateConfig(array $config): bool;

    /**
     * Get available models for this provider
     */
    public function getAvailableModels(): array;
}
