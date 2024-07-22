<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;

#[Attribute]
final readonly class Singleton
{
    public function __construct(
        public ?string $tag = null,
    ) {
    }
}
