<?php

namespace Tempest\Validation\Tests\Fixtures;

use Tempest\Validation\SkipValidation;

final class ObjectWithSkipValidation
{
    #[SkipValidation]
    public string $prop;
}
