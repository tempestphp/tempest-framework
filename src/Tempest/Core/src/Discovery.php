<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Reflection\ClassReflector;

interface Discovery
{
    public function discover(ClassReflector $class): void;

    public function createCachePayload(): string;

    public function restoreCachePayload(Container $container, string $payload): void;
}
