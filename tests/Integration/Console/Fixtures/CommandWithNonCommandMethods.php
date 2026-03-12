<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\ConsoleCommand;

final class CommandWithNonCommandMethods
{
    public function __invoke(): void
    {
    }

    #[ConsoleCommand('test:not-empty')]
    public function do(): void
    {
    }

    public function empty(): void
    {
    }
}
