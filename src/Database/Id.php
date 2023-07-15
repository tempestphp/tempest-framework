<?php

declare(strict_types=1);

namespace Tempest\Database;

final readonly class Id
{
    public function __construct(
        public string|int $id,
    ) {
    }
}
