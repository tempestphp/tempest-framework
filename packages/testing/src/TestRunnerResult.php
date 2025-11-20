<?php

namespace Tempest\Testing;

final class TestRunnerResult
{
    public function __construct(
        public int $succeeded = 0,
        public int $failed = 0,
    ) {}

    public function success(): self
    {
        $this->succeeded += 1;

        return $this;
    }

    public function fail(): self
    {
        $this->failed += 1;

        return $this;
    }
}