<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

class BuiltinTypesWithDefaultsClass
{
    public function __construct(
        public string $aString = 'This is a default value',
    ) {
    }
}
