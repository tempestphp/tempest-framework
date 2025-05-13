<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class ConsoleArgument
{
    public array $aliases;

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public string $help = '',
        array $aliases = [],
    ) {
        foreach ($aliases as $key => $alias) {
            if (strlen($alias) === 1) {
                $aliases[$key] = "-{$alias}";
            }
        }

        $this->aliases = $aliases;
    }
}
