<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface CommandBus
{
    public function dispatch(object $command): void;

    public function getHistory(): array;
}
