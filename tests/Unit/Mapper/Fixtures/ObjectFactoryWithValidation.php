<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

use Tempest\ORM\IsModel;
use Tempest\ORM\Model;
use Tempest\Validation\Rules\Length;

class ObjectFactoryWithValidation implements Model
{
    use IsModel;

    #[Length(min: 2)]
    public string $prop;
}
