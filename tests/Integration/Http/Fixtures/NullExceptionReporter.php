<?php

namespace Tests\Tempest\Integration\Http\Fixtures;

use Tempest\Container\Singleton;
use Tempest\Core\Exceptions\ExceptionReporter;
use Tempest\Discovery\SkipDiscovery;
use Throwable;

#[SkipDiscovery]
#[Singleton]
final class NullExceptionReporter implements ExceptionReporter
{
    public static array $exceptions = [];

    public function report(Throwable $throwable): void
    {
        static::$exceptions[] = $throwable;
    }
}
