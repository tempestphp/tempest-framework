<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules\Fixtures;

enum SomeBackedEnum: string
{
    case Test = 'one';
    case Test2 = 'two';
}
