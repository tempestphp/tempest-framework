<?php

namespace Tempest\Validation\Tests\Fixtures;

use Tempest\Validation\Rules\Length;

final class ValidateObjectC
{
    #[Length(min: 2)]
    public string $name;
}
