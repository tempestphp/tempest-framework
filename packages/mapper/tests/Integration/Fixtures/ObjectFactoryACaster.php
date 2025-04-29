<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use Tempest\Mapper\Caster;

final class ObjectFactoryACaster implements Caster
{
    public function cast(mixed $input): string
    {
        return 'casted';
    }

    public function serialize(mixed $input): string
    {
        return $input;
    }
}
