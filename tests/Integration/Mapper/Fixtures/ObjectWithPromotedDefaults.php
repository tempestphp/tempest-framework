<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ObjectWithPromotedDefaults
{
    public static int $constructorCalls = 0;

    public function __construct(
        public readonly string $name = 'default-name',
        public readonly int $count = 5,
    ) {
        self::$constructorCalls++;
    }
}
