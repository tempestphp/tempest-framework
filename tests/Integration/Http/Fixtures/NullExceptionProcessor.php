<?php

namespace Tests\Tempest\Integration\Http\Fixtures;

use Tempest\Core\ExceptionProcessor;
use Tempest\Discovery\SkipDiscovery;
use Throwable;

#[SkipDiscovery]
final class NullExceptionProcessor implements ExceptionProcessor
{
    public static array $exceptions = [];

    public function process(Throwable $throwable): void
    {
        static::$exceptions[] = $throwable;
    }
}
