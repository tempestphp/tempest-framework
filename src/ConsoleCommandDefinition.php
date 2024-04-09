<?php

declare(strict_types=1);

namespace Tempest\Console;

final readonly class ConsoleCommandDefinition
{
    public function __construct(
        /** @var ConsoleArgumentDefinition[] */
        public array $argumentDefinitions,
    ) {
    }
}
