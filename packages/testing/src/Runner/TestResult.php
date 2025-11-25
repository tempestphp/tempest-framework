<?php

namespace Tempest\Testing\Runner;

final class TestResult
{
    public function __construct(
        public int $succeeded = 0,
        public int $failed = 0,
        public int $skipped = 0,
    ) {}

    private ?float $startTime = null;
    private ?float $endTime = null;

    public float $elapsedTime {
        get => round($this->endTime - $this->startTime, 2);
    }

    public function startTime(): self
    {
        $this->startTime = microtime(true);

        return $this;
    }

    public function endTime(): self
    {
        $this->endTime = microtime(true);

        return $this;
    }

    public function addSucceeded(): self
    {
        $this->succeeded += 1;

        return $this;
    }

    public function addFailed(): self
    {
        $this->failed += 1;

        return $this;
    }

    public function addSkipped(): self
    {
        $this->skipped += 1;

        return $this;
    }
}