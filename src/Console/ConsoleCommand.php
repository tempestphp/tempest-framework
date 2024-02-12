<?php

namespace Tempest\Console;

use Attribute;

#[Attribute]
class ConsoleCommand
{
    public function __construct(
        public ?string $name = null,
    ) {}
}