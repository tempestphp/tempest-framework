<?php

namespace Tempest\Framework\Testing;

use DateTime as PHPDateTime;
use Tempest\Database\PrimaryKey;
use Tempest\DateTime\DateTime;
use Tempest\Reflection\PropertyReflector;

use function Tempest\Mapper\map;
use function Tempest\Reflection\reflect;
use function Tempest\Support\arr;

/** @template TModelClass */
final class ModelFactory
{
    private array $fields = [];

    public function __construct(
        /** @var class-string<TModelClass> The model class to create an instance of. */
        private readonly string $modelClass,
    ) {}

    /**
     * Make multiple instances of the model class.
     *
     * @param int|array $items The number of instances to make, or an array with field values used as a sequence to generate multiple instances.
     *
     * @return ModelFactoryCollection<TModelClass>
     */
    public function times(int|array $items): ModelFactoryCollection
    {
        return new ModelFactoryCollection($this, $items);
    }

    /**
     * Set up values that should be used for specific fields when creating a model instance
     *
     * @param mixed ...$fields If another instance of a ModelFactory is passed, it will be used to create the value for that property.
     *
     * @return self<TModelClass>
     */
    public function with(mixed ...$fields): self
    {
        return clone($this, [
            'fields' => [
                ...$this->fields,
                ...$fields,
            ],
        ]);
    }

    /**
     * Make an instance of the model class.
     *
     * @return TModelClass
     */
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

            $value = $this->generateValue($property);

            if ($value === null && $property->isNullable()) {
                $property->setValue($model, null);

                continue;
            }

            if ($value === null) {
                continue;
            }

            $property->setValue($model, $value);
        }

        return $model;
    }

    /**
     * Make an instance of the model class and save it to the database.
     *
     * @return TModelClass
     */
    public function save()
    {
        $model = $this->make();

        $model->save();

        return $model;
    }

    private function generateValue(PropertyReflector $property): mixed
    {
        $type = $property->getType();

        if ($type->matches(PrimaryKey::class)) {
            return null;
        }

        if ($type->matches(DateTime::class)) {
            return DateTime::now()->plusHours(random_int(-24, 24));
        }

        if ($type->matches(PHPDateTime::class)) {
            return DateTime::now()->plusHours(random_int(-24, 24))->toNativeDateTime();
        }

        if ($type->isEnum()) {
            $cases = arr($type->getName()::cases());

            return $cases->random();
        }

        if ($type->isClass()) {
            return new self($type->getName())->make();
        }

        return match ($type->getName()) {
            'string' => arr(['Lorem', 'Ipsum', 'Dolor', 'Sit', 'Amet'])->random(),
            'int' => random_int(1, 100),
            'float' => random_int(100, 1000) / 10,
            'bool' => arr([true, false])->random(),
            default => null,
        };
    }
}
