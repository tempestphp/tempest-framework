<?php

namespace Tempest\Database\Casters;

use Tempest\Core\Priority;
use Tempest\Database\DatabaseContext;
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Context as MapperContext;
use Tempest\Mapper\DynamicCaster;
use Tempest\Mapper\Exceptions\ValueCouldNotBeCast;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\SerializeAs;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Arr;
use Tempest\Support\Json;

use function Tempest\Mapper\map;

#[Priority(Priority::HIGHEST)]
#[Context(DatabaseContext::class)]
final readonly class DataTransferObjectCaster implements Caster, DynamicCaster
{
    public function __construct(
        private MapperConfig $mapperConfig,
        private MapperContext $context,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $type): bool
    {
        $type = $type instanceof PropertyReflector
            ? $type->getType()
            : $type;

        if ($type->isUnion()) {
            foreach ($type->split() as $memberType) {
                if (static::accepts($memberType)) {
                    return true;
                }
            }

            return false;
        }

        return $type->isClass() && $type->asClass()->getAttribute(SerializeAs::class) !== null;
    }

    public function cast(mixed $input): mixed
    {
        if (is_string($input) && Json\is_valid($input)) {
            return $this->deserialize(Json\decode($input));
        }

        if (is_array($input)) {
            return $this->deserialize($input);
        }

        if (is_string($input)) {
            throw new ValueCouldNotBeCast('json string');
        }

        return $input;
    }

    private function deserialize(mixed $input): mixed
    {
        if (is_array($input) && isset($input['type'], $input['data'])) {
            $class = Arr\find_key(
                array: $this->mapperConfig->serializationMap,
                value: $input['type'],
            ) ?: $input['type'];

            return map($this->deserialize($input['data']))
                ->in($this->context)
                ->to($class);
        }

        if (is_array($input)) {
            return array_map(fn (mixed $value) => $this->deserialize($value), $input);
        }

        return $input;
    }
}
