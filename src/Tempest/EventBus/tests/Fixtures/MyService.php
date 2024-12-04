<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Fixtures;

final class MyService
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}
