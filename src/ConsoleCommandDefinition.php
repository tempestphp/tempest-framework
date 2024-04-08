<?php

declare(strict_types=1);

namespace Tempest\Console;

final class ConsoleCommandDefinition
{
    public function __construct(
        /** @var ConsoleArgumentDefinition[] */
        public readonly array $argumentDefinitionList,
    ) {

    }
}
