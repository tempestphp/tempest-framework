<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;

#[Attribute]
class ConsoleCommand
{
    public function __construct(
        public ?string $name = null,
    ) {
    }
}
