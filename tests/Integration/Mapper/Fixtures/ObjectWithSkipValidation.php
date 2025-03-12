<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Validation\SkipValidation;

final class ObjectWithSkipValidation
{
    #[SkipValidation]
    public string $prop;
}