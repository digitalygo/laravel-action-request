<?php

declare(strict_types=1);

namespace Tests;

use Digitalygo\ActionRequest\ActionRequestServiceProvider;
use Digitalygo\ActionRequest\Console\MakeActionRequestCommand;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected Filesystem $filesystem;

    public MakeActionRequestCommand $command;

    protected string $basePath;

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            ActionRequestServiceProvider::class,
        ];
    }
}
