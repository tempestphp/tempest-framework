<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus\Fixtures;

use Tempest\CommandBus\AsyncCommand;

#[AsyncCommand]
final readonly class MyAsyncCommand
{
    public function __construct(
        public string $name,
    ) {
    }
}
