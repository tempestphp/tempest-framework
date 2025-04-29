<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final class OptionalTypesClass
{
    public function __construct(
        public ?string $aString,
    ) {}
}
