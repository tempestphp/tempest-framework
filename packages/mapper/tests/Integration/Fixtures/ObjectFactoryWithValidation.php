<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\Length;

final class ObjectFactoryWithValidation
{
    use IsDatabaseModel;

    #[Length(min: 2)]
    public string $prop;
}
