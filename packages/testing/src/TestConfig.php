<?php

namespace Tempest\Testing;

use Tempest\Reflection\MethodReflector;

final class TestConfig
{
    public function __construct(
        /** @var \Tempest\Testing\Test[] */
        public array $tests = [],
    ) {}

    public function addTest(Test $test, MethodReflector $handler): self
    {
        $test->handler = $handler;

        $this->tests[] = $test;

        return $this;
    }
}