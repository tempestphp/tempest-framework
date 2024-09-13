<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

class OptionalTypesClass
{
    public function __construct(
        public ?string $aString
    ) {
    }
}
