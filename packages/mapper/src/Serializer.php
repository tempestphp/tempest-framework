<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;

interface Serializer
{
    /**
     * Declares what this serializer can handle.
     * Can return:
     * - A string type name: 'bool', 'int', 'string', etc.
     * - A class name: DateTime::class
     * - A Closure for complex matching: fn (TypeReflector $type) => $type->matches(SomeClass::class)
     *
     * @return false|string[]|string|(Closure(TypeReflector):bool)
     */
    public static function for(): false|array|string|Closure;

    /**
     * Serializes the given input into a string, array, or integer.
     */
    public function serialize(mixed $input): array|string|int;
}
