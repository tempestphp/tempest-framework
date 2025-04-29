<?php

declare(strict_types=1);

namespace Tempest\CommandBus\Tests\Integration\Fixtures;

use Tempest\CommandBus\AsyncCommand;

#[AsyncCommand]
final readonly class MyFailingAsyncCommand
{
    public function __construct(
        public string $name,
    ) {}
}
