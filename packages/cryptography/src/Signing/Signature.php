<?php

namespace Tempest\Cryptography\Signing;

use Stringable;

final readonly class Signature implements Stringable
{
    public function __construct(
        public string $value,
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }

    public static function from(string $value): self
    {
        return new self($value);
    }
}
