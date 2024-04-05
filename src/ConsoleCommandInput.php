<?php

declare(strict_types=1);

namespace Tempest\Console;

final class ConsoleCommandInput
{
    public function __construct(
        /** @var ConsoleInputArgument[] */
        public array $arguments = [],
    ) {

    }
}
