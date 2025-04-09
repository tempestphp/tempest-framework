<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\Between;
use Tempest\Validation\SkipValidation;

final class ModelWithValidation
{
    use IsDatabaseModel;

    #[Between(min: 1, max: 10)]
    public int $index;

    #[SkipValidation]
    public int $skip;
}
