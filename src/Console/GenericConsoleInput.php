<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\ConsoleInput;

final readonly class GenericConsoleInput implements ConsoleInput
{
    use BaseConsoleInput;
}
