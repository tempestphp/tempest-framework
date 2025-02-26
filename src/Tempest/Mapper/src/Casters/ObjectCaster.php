<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Stringable;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Reflection\TypeReflector;
use Throwable;

final readonly class ObjectCaster implements Caster
{
    public function __construct(
        private TypeReflector $type,
    ) {
    }

    public function cast(mixed $input): mixed
    {
        try {
            return $this->type->asClass()->newInstanceArgs([$input]);
        } catch (Throwable) {
            return $input;
        }
    }

    public function serialize(mixed $input): string
    {
        if (! is_object($input)) {
            throw new CannotSerializeValue('object');
        }

        if ($input instanceof Stringable) {
            return (string) $input;
        }

        return serialize($input);
    }
}
