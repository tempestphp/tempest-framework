<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Exception;
use Tempest\Reflection\MethodReflector;

final class CommandHandlerAlreadyExists extends Exception
{
    public function __construct(string $commandName, MethodReflector $new, MethodReflector $existing)
    {
        parent::__construct("Cannot add handler {$new->getShortName()}, {$existing->getShortName()} already handles {$commandName}.");
    }
}
