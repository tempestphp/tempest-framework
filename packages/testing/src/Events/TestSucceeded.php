<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\HandleOnce;

final readonly class TestSucceeded implements DispatchToParentProcess, HandleOnce
{
    public function __construct(
        public string $name,
    ) {}
}