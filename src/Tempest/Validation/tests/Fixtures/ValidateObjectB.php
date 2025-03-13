<?php

namespace Tempest\Validation\Tests\Fixtures;

use Tempest\Validation\Rules\Length;

final class ValidateObjectB
{
    public ValidateObjectC $c;

    #[Length(min: 2)]
    public string $name;

    public int $age;
}
