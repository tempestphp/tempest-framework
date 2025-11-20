<?php

namespace Tempest\Testing\Events;

final readonly class TestSkipped
{
    public function __construct(
        public string $name,
    ) {}
}