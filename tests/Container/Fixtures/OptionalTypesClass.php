<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

class OptionalTypesClass
{
    public function __construct(
        public ?string $aString
    ) {
    }
}
