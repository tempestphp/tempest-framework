<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\StopsPropagation;

#[StopsPropagation]
final readonly class TestsChunked
{
    public function __construct(
        public int $processCount,
    ) {}
}
