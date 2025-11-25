<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\HandleOnce;

final readonly class TestsChunked implements HandleOnce
{
    public function __construct(
        public int $processCount,
    ) {}
}