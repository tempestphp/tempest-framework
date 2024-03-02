<?php

declare(strict_types=1);

namespace Tempest\Container;

final class ContainerConfig
{
    public function __construct(
        public array $definitions = [],
        public array $singletons = [],
        /** @var (Initializer&CanInitialize)[] $initializers */
        public array $initializers = [],
    ) {
    }
}
