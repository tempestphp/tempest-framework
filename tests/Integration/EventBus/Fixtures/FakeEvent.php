<?php

namespace Tests\Tempest\Integration\EventBus\Fixtures;

final readonly class FakeEvent
{
    public function __construct(
        public string $value,
    ) {}
}
