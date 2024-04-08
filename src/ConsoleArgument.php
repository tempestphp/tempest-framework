<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class ConsoleArgument
{
    public function __construct(
        public readonly ?string $description = null,
        public readonly string $help = '',
        public readonly array $aliases = [],
    ) {
    }
}
