<?php

declare(strict_types=1);

namespace Tempest\ORM;

final readonly class QueryValue
{
    public function __construct(
        public mixed $value,
    ) {

    }
}
