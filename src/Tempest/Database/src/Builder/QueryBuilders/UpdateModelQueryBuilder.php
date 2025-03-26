<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Query;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;

/**
 * @template TModelClass of object
 */
final readonly class UpdateModelQueryBuilder
{
    public function __construct(
        private object $model,
        private SerializerFactory $serializerFactory,
    ) {}

    public function build(): Query
    {
        $modelClass = new ClassReflector($this->model);
        $modelDefinition = new ModelDefinition($this->model);

        $fields = $this->fields($modelClass, $this->model);

        unset($fields['id']);

        $values = implode(', ', array_map(
            fn (string $key) => "{$key} = :{$key}",
            array_keys($fields),
        ));

        // TODO: update relations?

        $fields['id'] = $this->model->id;

        $table = $modelDefinition->getTableDefinition();

        return new Query(
            "UPDATE {$table} SET {$values} WHERE id = :id;",
            $fields,
        );
    }

    private function fields(ClassReflector $modelClass, object $model): array
    {
        $fields = [];

        foreach ($modelClass->getPublicProperties() as $property) {
            if (! $property->isInitialized($model)) {
                continue;
            }

            // 1:1 or n:1 relations
            if ($property->getType()->isRelation()) {
                continue;
            }

            // 1:n relations
            if ($property->getIterableType()?->isRelation()) {
                continue;
            }

            $value = $property->getValue($model);

            // Check if serializer is available for value serialization
            if ($value !== null && ($serializer = $this->serializerFactory->forProperty($property))) {
                $value = $serializer->serialize($value);
            }

            $fields[$property->getName()] = $value;
        }

        return $fields;
    }
}
