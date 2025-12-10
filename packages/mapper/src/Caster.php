<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Reflection\TypeReflector;

interface Caster
{
    /**
     * Declares what this caster can handle.
     *
     * Can return:
     * - A string type name: 'bool', 'int', 'string', etc.
     * - A class name: DateTime::class
     * - A Closure for complex matching: fn (TypeReflector $type) => $type->matches(SomeClass::class)
     *
     * @return false|string|array|(Closure(TypeReflector):bool)
     */
    public static function for(): false|array|string|Closure;

    /**
     * Creates an object or a scalar value from the given input.
     */
    public function cast(mixed $input): mixed;
}
