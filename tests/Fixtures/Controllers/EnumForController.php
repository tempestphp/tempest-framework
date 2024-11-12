<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

enum EnumForController: string
{
    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}
