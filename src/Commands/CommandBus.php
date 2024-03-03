<?php

declare(strict_types=1);

namespace Tempest\Commands;

interface CommandBus
{
    public function dispatch(object $command): void;

    public function getHistory(): array;
}
