<?php

namespace Tempest\Framework\Testing;

use Tempest\Reflection\PropertyReflector;

use function Tempest\Mapper\map;
use function Tempest\Reflection\reflect;
use function Tempest\Support\arr;

/** @template TModelClass */
final class ModelFactory
{
    private array $fields = [];

    public function __construct(
        /** @var class-string<TModelClass> */
        private readonly string $modelClass,
    ) {}

    /** @return ModelFactoryCollection<TModelClass> */
    public function times(int|array $items): ModelFactoryCollection
    {
        return new ModelFactoryCollection($this, $items);
    }

    /** @return self<TModelClass> */
    public function with(mixed ...$properties): self
    {
        return clone($this, [
            'fields' => [
                ...$this->fields,
                ...$properties,
            ],
        ]);
    }

    /** @return TModelClass */
    public function make()
    {
        $fields = $this->fields;

        foreach ($fields as $key => $value) {
            if (! $value instanceof ModelFactory) {
                continue;
            }

            $fields[$key] = $value->make();
        }

        $model = map($fields)->to($this->modelClass);

        foreach (reflect($model)->getPublicProperties() as $property) {
            if ($property->isInitialized($model)) {
                continue;
            }

            if ($property->hasDefaultValue()) {
                continue;
            }

            if ($property->isNullable()) {
                $property->setValue($model, null);

                continue;
            }

            $value = $this->generateValue($property);

            if ($value === null) {
                continue;
            }

            $property->setValue($model, $value);
        }

        return $model;
    }

    /** @return TModelClass */
    public function save()
    {
        $model = $this->make();

        $model->save();

        return $model;
    }

    private function generateValue(PropertyReflector $property): mixed
    {
        return match ($property->getType()->getName()) {
            'string' => arr(['Lorem', 'Ipsum', 'Dolor', 'Sit', 'Amet'])->random(),
            'int' => random_int(1, 100),
            'float' => random_int(100, 1000) / 10,
            'bool' => arr([true, false])->random(),
            default => null,
        };
    }
}
