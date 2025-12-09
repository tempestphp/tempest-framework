<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Closure;
use Tempest\Mapper\Caster;

final class ObjectFactoryACaster implements Caster
{
    public static function for(): string
    {
        return 'string';
    }

    public function cast(mixed $input): string
    {
        return 'casted';
    }

    public function serialize(mixed $input): string
    {
        return $input;
    }
}
