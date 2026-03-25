<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class ConsoleArgument
{
    /**
     * @param null|string $name The name of the argument. If not provided, the name of the associated parameter or property will be used.
     * @param null|string $description A short description explaining what this argument does.
     * @param null|string $help Detailed information displayed when displayed help for the associated command.
     * @param null|string $prompt A prompt displayed when prompting a user for this argument.
     * @param array<int, string> $aliases Alternative names for this argument.
     */
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $prompt = null,
        public ?string $help = null,
        public array $aliases = [],
    ) {}
}
