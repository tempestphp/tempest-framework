<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

use DateTime;

class UnionTypesClass
{
    public function __construct(
        public string|DateTime $aStringOrDate
    ) {
    }
}
