<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\Length;

class ObjectFactoryWithValidation implements DatabaseModel
{
    use IsDatabaseModel;

    #[Length(min: 2)]
    public string $prop;
}
