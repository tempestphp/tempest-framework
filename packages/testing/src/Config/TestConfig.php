<?php

namespace Tempest\Testing\Config;

use Tempest\Testing\Test;

final class TestConfig
{
    public function __construct(
        /** @var \Tempest\Testing\Test[] */
        public array $tests = [],
    ) {}

    public function addTest(Test $test): self
    {
        $this->tests[] = $test;

        return $this;
    }
}
