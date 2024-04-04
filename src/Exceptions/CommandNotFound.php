<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Exception;

final class CommandNotFound extends Exception
{
    public function __construct(string $commandName)
    {
        parent::__construct("Command `{$commandName}` not found");
    }
}
