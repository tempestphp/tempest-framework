<?php

namespace Tempest\EventBus\Tests\Integration\Fixtures;

final readonly class FakeEvent
{
    public function __construct(
        public string $value,
    ) {}
}
