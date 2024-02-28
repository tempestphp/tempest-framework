<?php

declare(strict_types=1);

namespace Tempest\Commands;

use Tempest\Container\InitializedBy;

#[InitializedBy(CommandBusInitializer::class)]
interface CommandBus
{
    public function dispatch(object $command): void;

    public function getHistory(): array;
}
