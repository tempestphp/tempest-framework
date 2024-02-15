<?php

declare(strict_types=1);

namespace Tempest\Interface;

use ReflectionClass;

interface Discovery
{
    public function discover(ReflectionClass $class): void;

    public function hasCache(): bool;

    public function storeCache(): void;

    public function restoreCache(Container $container): void;

    public function destroyCache(): void;
}
