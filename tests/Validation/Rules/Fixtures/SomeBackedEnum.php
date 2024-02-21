<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules\Fixtures;

enum SomeBackedEnum: string
{
    case Test = 'one';
    case Test2 = 'two';
}
