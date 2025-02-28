<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use BackedEnum;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use UnitEnum;

final readonly class EnumCaster implements Caster
{
    public function __construct(private string $enum)
    {
    }

    public function cast(mixed $input): ?object
    {
        if ($input instanceof $this->enum) {
            return $input;
        }

        if ($input === null) {
            return null;
        }

        return forward_static_call("{$this->enum}::from", $input);
    }

    public function serialize(mixed $input): string
    {
        if ($input instanceof BackedEnum) {
            return (string) $input->value;
        }

        if ($input instanceof UnitEnum) {
            return $input->name;
        }

        throw new CannotSerializeValue('enum');
    }
}
