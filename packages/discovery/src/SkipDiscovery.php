<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Attribute;
use Closure;

/**
 * Instruct Tempest to not discover this class.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SkipDiscovery
{
    public function __construct(
        /**
         * Allows the specified `Discovery` classes to still discover this class.
         * Pass a closure to dynamically determine if the class should be discovered.
         * @var array<class-string<\Tempest\Discovery\Discovery>>|Closure(\Tempest\Container\Container|\Psr\Container\ContainerInterface $container): bool
         */
        public Closure|array $except = [],
    ) {}
}
