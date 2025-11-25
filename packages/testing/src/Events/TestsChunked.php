<?php

namespace Tempest\Testing\Events;

final readonly class TestsChunked
{
    public function __construct(
        public int $processCount,
    ) {}
}