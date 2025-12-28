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
         * @param array<class-string<\Tempest\Discovery\Discovery>>
         * @param Closure|null
         */
        public array $except = [],
        public ?Closure $when = null,
    ) {}
}
