<?php

declare(strict_types=1);

namespace Digitalygo\LaravelActionRequest;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Digitalygo\LaravelActionRequest\Skeleton\SkeletonClass
 */
final class LaravelActionRequestFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-action-request';
    }
}
