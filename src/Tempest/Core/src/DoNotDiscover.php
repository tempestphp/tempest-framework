<?php

declare(strict_types=1);

namespace Tempest\Core;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DoNotDiscover
{
    public function __construct(
        /**
         * Allows the specified `Discovery` classes to still discover this class.
         * @var array<class-string<\Tempest\Core\Discovery>>
         */
        public readonly array $except = [],
    ) {
    }
}
