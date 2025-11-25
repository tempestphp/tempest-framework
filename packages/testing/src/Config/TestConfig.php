<?php

namespace Tempest\Testing\Config;

use Tempest\Reflection\MethodReflector;
use Tempest\Testing\Test;

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