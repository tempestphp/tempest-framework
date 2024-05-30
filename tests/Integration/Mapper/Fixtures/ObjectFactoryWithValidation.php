<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\IsModel;
use Tempest\Database\Model;
use Tempest\Validation\Rules\Length;

class ObjectFactoryWithValidation implements Model
{
    use IsModel;

    #[Length(min: 2)]
    public string $prop;
}
