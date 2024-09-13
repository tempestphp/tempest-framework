<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

class BuiltinTypesWithDefaultsClass
{
    public function __construct(
        public string $aString = 'This is a default value',
    ) {
    }
}
