<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class ConsoleArgument
{
    public function __construct(
        public ?string $description = null,
        public string $help = '',
        public array $aliases = [],
    ) {
    }
}
