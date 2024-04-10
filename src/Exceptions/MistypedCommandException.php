<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Exception;
use Tempest\Console\ConsoleCommand;

final class MistypedCommandException extends Exception
{
    public readonly ConsoleCommand $intendedCommand;

    public function __construct(ConsoleCommand $intendedCommand)
    {
        parent::__construct('Command not found');
        $this->intendedCommand = $intendedCommand;
    }

    public static function for(mixed $intendedCommand): MistypedCommandException
    {
        return new self($intendedCommand);
    }
}
