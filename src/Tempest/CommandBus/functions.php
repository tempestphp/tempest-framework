<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\CommandBus\CommandBus;

    function command(object $command): void
    {
        $commandBus = get(CommandBus::class);

        $commandBus->dispatch($command);
    }
}
