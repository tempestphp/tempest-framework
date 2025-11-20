<?php

namespace Tempest\Testing\Events;

final readonly class TestSucceeded
{
    public function __construct(
        public string $name,
    ) {}
}