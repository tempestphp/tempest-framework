<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Reflection\ClassReflector;

interface Discovery
{
    public function discover(ClassReflector $class): void;

    public function hasCache(): bool;

    public function storeCache(): void;

    public function restoreCache(Container $container): void;

    public function destroyCache(): void;
}
