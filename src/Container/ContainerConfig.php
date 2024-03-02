<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;

final class ContainerConfig
{
    public function __construct(
        public array $definitions = [],
        public array $singletons = [],
        /**
         * @template T of \Tempest\Container\Initializer
         * @var class-string<T> $initializers
         */
        public array $initializers = [],
    ) {
    }

    public function addInitializer(ReflectionClass|string $initializerClass): void
    {
    }
}
