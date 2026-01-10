<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Caster;

final class InterfaceValueCaster implements Caster
{
    public static function for(): string
    {
        return InterfaceWithCastWith::class;
    }

    public function cast(mixed $input): InterfaceWithCastWith
    {
        return new ConcreteInterfaceValue('casted:' . $input);
    }
}
