<?php

declare(strict_types=1);

namespace Tempest\Console;

final class ConsoleInputArgument
{
    public function __construct(
        public ?string $name,
        public mixed $value,
        public int $position,
    ) {
    }
}
