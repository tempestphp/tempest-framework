<?php

namespace Tempest\Database\Builder;

use ReflectionException;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\TableName;
use Tempest\Reflection\ClassReflector;

use function Tempest\get;

final class ModelInspector
{
    private ?ClassReflector $modelClass;

    public function __construct(
        private object|string $model,
    )
    {
        if ($this->model instanceof ClassReflector) {
            $this->modelClass = $this->model;
        } else {
            try {
                $this->modelClass = new ClassReflector($this->model);
            } catch (ReflectionException) {
                $this->modelClass = null;
            }
        }
    }

    public function isObjectModel(): bool
    {
        return $this->modelClass !== null;
    }

    public function getTableDefinition(): TableDefinition
    {
        if (! $this->isObjectModel()) {
            return new TableDefinition($this->model);
        }

        $specificName = $this->modelClass
            ->getAttribute(TableName::class)
            ?->name;

        $conventionalName = get(DatabaseConfig::class)
            ->namingStrategy
            ->getName($this->modelClass->getName());

        return new TableDefinition($specificName ?? $conventionalName);
    }

    public function getPropertyValues(): array
    {
        if (! $this->isObjectModel()) {
            return [];
        }

        if (! is_object($this->model)) {
            return [];
        }

        $values = [];

        foreach ($this->modelClass->getProperties() as $property) {
            if ($property->getIterableType()?->isRelation()) {
                continue;
            }

            $name = $property->getName();

            if ($property->getType()->isRelation()) {
                $name .= '_id';
            }

            $values[$name] = $property->getValue($this->model);
        }

        return $values;
    }
}
