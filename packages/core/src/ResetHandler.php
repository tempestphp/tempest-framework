<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;

class ResetHandler
{
    public function __construct(
        private readonly ResetableContainer $resetableContainer,
    ) {}

    public function reset(Container $container): void
    {
        foreach ($this->resetableContainer->resetableClasses as $class) {
            if (! $container->has($class)) {
                continue;
            }

            $container->get($class)->reset();
        }

        foreach ($this->resetableContainer->resetableStaticClasses as $class) {
            $class::resetStatic();
        }
    }
}
