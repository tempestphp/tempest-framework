<?php

declare(strict_types=1);

namespace Tests\Tempest\Container;

use DateTime;

class UnionTypesClass
{
    public function __construct(
        public DateTime $aStringOrDate
    ) {
    }
}
