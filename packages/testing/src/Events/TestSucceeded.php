<?php

namespace Tempest\Testing\Events;

final readonly class TestSucceeded implements DispatchToParentProcess
{
    public function __construct(
        public string $name,
    ) {}
}