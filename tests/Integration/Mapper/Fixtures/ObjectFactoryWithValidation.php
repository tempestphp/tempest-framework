<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Validation\Rules\HasLength;

final class ObjectFactoryWithValidation
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    #[HasLength(min: 2)]
    public string $prop;
}
