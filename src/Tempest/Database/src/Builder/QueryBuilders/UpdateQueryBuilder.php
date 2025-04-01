<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Exceptions\CannotUpdateHasManyRelation;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\UpdateStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Database\model;
use function Tempest\Support\arr;

final class UpdateQueryBuilder
{
    private UpdateStatement $update;

    private array $bindings = [];

    public function __construct(
        private string|object $model,
        private array|ImmutableArray $values,
        private SerializerFactory $serializerFactory,
    ) {
        $this->update = new UpdateStatement(
            table: model($this->model)->getTableDefinition(),
        );
    }

    public function execute(mixed ...$bindings): Id
    {
        return $this->build()->execute(...$bindings);
    }

    public function allowAll(): self
    {
        $this->update->allowAll = true;

        return $this;
    }

    public function where(string $where, mixed ...$bindings): self
    {
        $this->update->where[] = new WhereStatement($where);

        $this->bind(...$bindings);

        return $this;
    }

    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    public function build(): Query
    {
        $values = $this->resolveValues();

        unset($values['id']);

        $this->update->values = $values;

        if (model($this->model)->isObjectModel() && is_object($this->model)) {
            $this->where('`id` = ?', id: $this->model->id->id);
        }

        $bindings = [];

        foreach ($values as $value) {
            $bindings[] = $value;
        }

        foreach ($this->bindings as $binding) {
            $bindings[] = $binding;
        }

        return new Query($this->update, $bindings);
    }

    private function resolveValues(): ImmutableArray
    {
        if (! model($this->model)->isObjectModel()) {
            return arr($this->values);
        }

        $values = arr();

        $modelClass = new ClassReflector($this->model);

        foreach ($this->values as $column => $value) {
            $property = $modelClass->getProperty($column);

            if ($property->getIterableType()?->isRelation()) {
                throw new CannotUpdateHasManyRelation($modelClass->getName(), $property->getName());
            }

            if ($property->getType()->isRelation()) {
                $column .= '_id';

                $value = match (true) {
                    $value === null => null,
                    isset($value->id) => $value->id->id,
                    default => new InsertQueryBuilder(
                        $value::class,
                        [$value],
                        $this->serializerFactory,
                    )->build(),
                };
            }

            if (! $property->getType()->isRelation() && ! $property->getIterableType()?->isRelation()) {
                $serializer = $this->serializerFactory->forProperty($property);

                if ($value !== null && $serializer !== null) {
                    $value = $serializer->serialize($value);
                }
            }

            $values[$column] = $value;
        }

        return $values;
    }
}
