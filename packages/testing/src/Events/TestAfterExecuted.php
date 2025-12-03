<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\StopsPropagation;
use Tempest\Reflection\MethodReflector;
use Tempest\Testing\Test;

#[StopsPropagation]
final readonly class TestAfterExecuted
{
    public function __construct(
        public Test $test,
        public MethodReflector $after,
    ) {}
}
