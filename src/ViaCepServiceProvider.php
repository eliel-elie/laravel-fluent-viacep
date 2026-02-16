<?php

namespace ViaCep;

use Illuminate\Support\ServiceProvider;
use ViaCep\Client\ViaCepClient;

class ViaCepServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/viacep.php',
            'viacep'
        );

        $this->app->singleton('viacep', function ($app) {
            return new ViaCepClient;
        });

        $this->app->alias('viacep', ViaCepClient::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/viacep.php' => config_path('viacep.php'),
            ], 'viacep-config');
        }
    }
}
