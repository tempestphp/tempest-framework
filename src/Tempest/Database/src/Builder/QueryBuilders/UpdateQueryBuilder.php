<?php

namespace Tempest\Database\Builder\QueryBuilders;

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
    )
    {
        $this->update = new UpdateStatement(
            table: model($this->model)->getTableDefinition(),
        );
    }

    public function execute(...$bindings): Id
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

        foreach ($this->values as $key => $value) {
            $serializer = $this->serializerFactory->forProperty($modelClass->getProperty($key));

            // TODO: insert or update relations

            if ($value !== null && $serializer !== null) {
                $value = $serializer->serialize($value);
            }

            $values[$key] = $value;
        }

        return $values;
    }
}
