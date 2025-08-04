<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\SkipValidation;

final class ModelWithValidation
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    #[IsBetween(min: 1, max: 10)]
    public int $index;

    #[SkipValidation]
    public int $skip;
}
