<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Http\Input\InputStream;

final readonly class MemoryInputStream implements InputStream
{
    public function __construct(
        private array $data,
    ) {}

    public function parse(): array
    {
        return $this->data;
    }
}
