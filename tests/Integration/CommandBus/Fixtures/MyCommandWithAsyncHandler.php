<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus\Fixtures;

final readonly class MyCommandWithAsyncHandler
{
    public function __construct(
        public string $name,
    ) {}
}
