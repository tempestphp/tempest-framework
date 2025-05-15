<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use ReflectionException;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Table;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\get;

final readonly class ModelDefinition
{
    private ClassReflector $modelClass;

    public static function tryFrom(string|object $model): ?self
    {
        try {
            return new self($model);
        } catch (ReflectionException) {
            return null;
        }
    }

    public function __construct(string|object $model)
    {
        if ($model instanceof ClassReflector) {
            $this->modelClass = $model;
        } else {
            $this->modelClass = new ClassReflector($model);
        }
    }

    public function getTableDefinition(): TableDefinition
    {
        $specificName = $this->modelClass
            ->getAttribute(Table::class)
            ?->name;

        $conventionalName = get(DatabaseConfig::class)
            ->namingStrategy
            ->getName($this->modelClass->getName());

        return new TableDefinition($specificName ?? $conventionalName);
    }

    public function getFieldDefinition(string $name): FieldDefinition
    {
        return new FieldDefinition(
            tableDefinition: $this->getTableDefinition(),
            name: $name,
        );
    }

    /** @return ImmutableArray<array-key, \Tempest\Database\Builder\FieldDefinition> */
    public function getFieldDefinitions(): ImmutableArray
    {
        return FieldDefinition::all($this->modelClass);
    }
}
