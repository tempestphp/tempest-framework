<?php

namespace Tempest\Database\Indexes;

final readonly class Index
{
    /**
     * @param list<string> $columns
     */
    public function __construct(
        public string $name,
        public array $columns,
        public bool $unique = false,
    ) {}
}