<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ObjectWithMagicGetter
{
    public function __construct(
        public string $a,
    ) {
    }

    public function __get(string $name)
    {
        return 'magic';
    }
}
