<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

enum EnumToCast: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
