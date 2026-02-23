<?php

declare(strict_types=1);

namespace Digitalygo\ActionRequest;

use Digitalygo\ActionRequest\Console\MakeActionRequestCommand;
use Illuminate\Support\ServiceProvider;

final class ActionRequestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/action-request.php' => config_path('action-request.php'),
        ], 'action-request-config');

        $this->publishes([
            __DIR__.'/../stubs/action.stub' => base_path('stubs/action-request/action.stub'),
            __DIR__.'/../stubs/action-simple.stub' => base_path('stubs/action-request/action-simple.stub'),
            __DIR__.'/../stubs/request.stub' => base_path('stubs/action-request/request.stub'),
            __DIR__.'/../stubs/request-simple.stub' => base_path('stubs/action-request/request-simple.stub'),
            __DIR__.'/../stubs/test.stub' => base_path('stubs/action-request/test.stub'),
            __DIR__.'/../stubs/test-simple.stub' => base_path('stubs/action-request/test-simple.stub'),
        ], 'action-request-stubs');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/action-request.php', 'action-request');

        $this->commands([
            MakeActionRequestCommand::class,
        ]);
    }
}
