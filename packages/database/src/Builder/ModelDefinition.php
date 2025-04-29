<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use ReflectionException;
use Tempest\Database\BelongsTo;
use Tempest\Database\Builder\Relations\BelongsToRelation;
use Tempest\Database\Builder\Relations\HasManyRelation;
use Tempest\Database\Builder\Relations\HasOneRelation;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Eager;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
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

    /** @return \Tempest\Database\Builder\Relations\Relation[] */
    public function getRelations(string $relationName): array
    {
        $relations = [];
        $relationNames = explode('.', $relationName);
        $alias = $this->getTableDefinition()->name;
        $class = $this->modelClass;

        foreach ($relationNames as $relationNamePart) {
            $property = $class->getProperty($relationNamePart);

            if ($property->hasAttribute(HasMany::class)) {
                /** @var HasMany $relationAttribute */
                $relationAttribute = $property->getAttribute(HasMany::class);
                $relations[] = HasManyRelation::fromAttribute($relationAttribute, $property, $alias);
                $class = HasManyRelation::getRelationModelClass($property, $relationAttribute)->getType()->asClass();
                $alias .= ".{$property->getName()}";
            } elseif ($property->getType()->isIterable()) {
                $relations[] = HasManyRelation::fromInference($property, $alias);
                $class = $property->getIterableType()->asClass();
                $alias .= ".{$property->getName()}[]";
            } elseif ($property->hasAttribute(HasOne::class)) {
                $relations[] = new HasOneRelation($property, $alias);
                $class = $property->getType()->asClass();
                $alias .= ".{$property->getName()}";
            } elseif ($property->hasAttribute(BelongsTo::class)) {
                /** @var BelongsTo $relationAttribute */
                $relationAttribute = $property->getAttribute(BelongsTo::class);
                $relations[] = BelongsToRelation::fromAttribute($relationAttribute, $property, $alias);
                $class = $property->getType()->asClass();
                $alias .= ".{$property->getName()}";
            } else {
                $relations[] = BelongsToRelation::fromInference($property, $alias);
                $class = $property->getType()->asClass();
                $alias .= ".{$property->getName()}";
            }
        }

        return $relations;
    }

    /** @return \Tempest\Database\Builder\Relations\Relation[] */
    public function getEagerRelations(): array
    {
        $relations = [];

        foreach ($this->buildEagerRelationNames($this->modelClass) as $relationName) {
            foreach ($this->getRelations($relationName) as $relation) {
                $relations[$relation->getRelationName()] = $relation;
            }
        }

        return $relations;
    }

    private function buildEagerRelationNames(ClassReflector $class): array
    {
        $relations = [];

        foreach ($class->getPublicProperties() as $property) {
            if (! $property->hasAttribute(Eager::class)) {
                continue;
            }

            $relations[] = $property->getName();

            foreach ($this->buildEagerRelationNames($property->getType()->asClass()) as $childRelation) {
                $relations[] = "{$property->getName()}.{$childRelation}";
            }
        }

        return $relations;
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
