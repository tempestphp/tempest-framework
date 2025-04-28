<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Attribute;

/**
 * Instruct Tempest to not discover this class.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SkipDiscovery
{
    public function __construct(
        /**
         * Allows the specified `Discovery` classes to still discover this class.
         * @var array<class-string<\Tempest\Discovery\Discovery>>
         */
        public array $except = [],
    ) {}
}
