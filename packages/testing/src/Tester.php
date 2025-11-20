<?php

namespace Tempest\Testing;

use Tempest\Testing\Exceptions\TestHasFailed;

final readonly class Tester
{
    public function __construct(
        private mixed $subject,
    ) {}

    public function is(mixed $expected): self
    {
        if ($expected !== $this->subject) {
            throw new TestHasFailed("failed asserting that %s is %s", $this->subject, $expected);
        }

        return $this;
    }
}