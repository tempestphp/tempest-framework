<?php

namespace Tempest\Database;

final readonly class Id
{
    public function __construct(
        public string|int $id,
    ) {}
}