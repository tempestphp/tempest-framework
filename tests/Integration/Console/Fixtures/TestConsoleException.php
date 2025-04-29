<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\Exceptions\ConsoleException;

final class TestConsoleException extends ConsoleException
{
    public function render(Console $_console): void
    {
    }
}
