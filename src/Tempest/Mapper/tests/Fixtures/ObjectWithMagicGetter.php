<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Fixtures;

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
