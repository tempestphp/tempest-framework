<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use RuntimeException;
use Tempest\Console\Console;

abstract class ConsoleException extends RuntimeException
{
    abstract public function render(Console $console): void;
}
