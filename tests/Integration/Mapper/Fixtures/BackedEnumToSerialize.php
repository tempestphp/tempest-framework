<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

enum BackedEnumToSerialize: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
