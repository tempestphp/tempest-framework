<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Stringable;
use Tempest\Mapper\SerializeWith;

#[SerializeWith(DoubleStringSerializer::class)]
final readonly class DoubleStringObject implements Stringable
{
    public function __construct(private string $value) {}

    public function __toString(): string
    {
        return $this->value;
    }
}