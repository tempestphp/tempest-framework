<?php

namespace Tempest\Testing;

use Tempest\Reflection\MethodReflector;

final class TestsConfig
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