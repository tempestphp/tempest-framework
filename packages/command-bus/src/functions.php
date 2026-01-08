<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\CommandBus\CommandBus;
use Tempest\Container;

/**
 * Dispatches the given `$command` to the {@see CommandBus}, triggering all associated command handlers.
 */
function command(object $command): void
{
    $commandBus = Container\get(CommandBus::class);

    $commandBus->dispatch($command);
}
