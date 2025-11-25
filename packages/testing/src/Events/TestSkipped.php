<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\HandleOnce;

final readonly class TestSkipped implements HandleOnce
{
    public function __construct(
        public string $name,
    ) {}
}