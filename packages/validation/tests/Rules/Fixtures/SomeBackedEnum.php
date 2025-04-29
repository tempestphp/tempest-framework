<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules\Fixtures;

enum SomeBackedEnum: string
{
    case Test = 'one';
    case Test2 = 'two';
}
