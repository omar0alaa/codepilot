<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Ai\AiProviderFactory;

class AiServiceProvider extends ServiceProvider
{
    /**
     * Register AI services
     */
    public function register(): void
    {
        $this->app->singleton(AiProviderFactory::class, function ($app) {
            return new AiProviderFactory();
        });

        // Bind the default AI provider
        $this->app->bind('ai.provider', function ($app) {
            $factory = $app->make(AiProviderFactory::class);
            $defaultProvider = config('ai.default', 'groq');
            return $factory->create($defaultProvider, config("ai.providers.{$defaultProvider}", []));
        });
    }

    /**
     * Bootstrap AI services
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/ai.php' => config_path('ai.php'),
        ], 'ai-config');
    }
}
