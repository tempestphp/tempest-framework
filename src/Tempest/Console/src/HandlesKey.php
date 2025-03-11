<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class HandlesKey
{
    public function __construct(
        public ?Key $key = null,
    ) {
    }
}
