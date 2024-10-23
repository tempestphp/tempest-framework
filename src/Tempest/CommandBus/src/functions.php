<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\CommandBus\CommandBus;

    /**
     * Dispatches the given `$command` to the {@see CommandBus}, triggering all associated command handlers.
     */
    function command(object $command): void
    {
        $commandBus = get(CommandBus::class);

        $commandBus->dispatch($command);
    }
}
