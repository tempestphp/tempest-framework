<?php

namespace Tempest\Testing;

use Tempest\Reflection\MethodReflector;

final class TestConfig
{
    public function __construct(
        /** @var MethodReflector[] */
        public array $tests = [],
    ) {}

    public function addTest(MethodReflector $handler): void
    {
        $this->tests[] = $handler;
    }
}