<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Router\Input\PostInputStream;

final readonly class MemoryPostInputStream implements PostInputStream
{
    public function __construct(
        private array $data,
    ) {}

    public function parse(): array
    {
        return $this->data;
    }
}