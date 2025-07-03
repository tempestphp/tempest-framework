<?php

namespace Tempest\Cryptography\Signing;

use Stringable;

final readonly class Signature implements Stringable
{
    public function __construct(
        public string $signature,
    ) {}

    public function __toString(): string
    {
        return $this->signature;
    }
}
