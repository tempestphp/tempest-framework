<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\ORM\Caster;

class ObjectFactoryACaster implements Caster
{
    public function cast(mixed $input): string
    {
        return 'casted';
    }
}
