<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

enum MyEnum: string
{
    case FOO = 'Foo';
    case BAR = 'Bar';
    case OTHER = 'Other';
}
