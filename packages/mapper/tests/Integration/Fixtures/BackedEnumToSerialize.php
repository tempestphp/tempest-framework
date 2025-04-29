<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

enum BackedEnumToSerialize: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
