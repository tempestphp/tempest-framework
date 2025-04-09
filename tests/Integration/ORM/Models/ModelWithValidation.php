<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\Between;

final class ModelWithValidation
{
    use IsDatabaseModel;

    #[Between(min: 1, max: 10)]
    public int $index;
}
