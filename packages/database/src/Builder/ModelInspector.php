<?php

namespace Tempest\Database\Builder;

use ReflectionException;
use Tempest\Database\BelongsTo;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Eager;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Database\Relation;
use Tempest\Database\Table;
use Tempest\Database\Virtual;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeWith;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\SkipValidation;
use Tempest\Validation\Validator;

use function Tempest\Database\model;
use function Tempest\get;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class ModelInspector
{
    private ?ClassReflector $modelClass;

    private object|string $model;

    public function __construct(object|string $model)
    {
        if ($model instanceof HasMany) {
            $model = $model->property->getIterableType()->asClass();
            $this->modelClass = $model;
        } elseif ($model instanceof BelongsTo || $model instanceof HasOne) {
            $model = $model->property->getType()->asClass();
            $this->modelClass = $model;
        } elseif ($model instanceof ClassReflector) {
            $this->modelClass = $model;
        } else {
            try {
                $this->modelClass = new ClassReflector($model);
            } catch (ReflectionException) {
                $this->modelClass = null;
            }
        }

        $this->model = $model;
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
            ->getAttribute(Table::class)
            ?->name;

        $conventionalName = get(DatabaseConfig::class)
            ->namingStrategy
            ->getName($this->modelClass->getName());

        return new TableDefinition($specificName ?? $conventionalName);
    }

    public function getTableName(): string
    {
        return $this->getTableDefinition()->name;
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
            if (! $property->isInitialized($this->model)) {
                continue;
            }

            if ($this->getHasMany($property->getName()) || $this->getHasOne($property->getName())) {
                continue;
            }

            $name = $property->getName();

            $values[$name] = $property->getValue($this->model);
        }

        return $values;
    }

    public function getBelongsTo(string $name): ?BelongsTo
    {
        if (! $this->isObjectModel()) {
            return null;
        }

        $name = str($name)->camel();

        $singularizedName = $name->singularizeLastWord();

        if (! $singularizedName->equals($name)) {
            return $this->getBelongsTo($singularizedName);
        }

        if (! $this->modelClass->hasProperty($name)) {
            return null;
        }

        $property = $this->modelClass->getProperty($name);

        if ($belongsTo = $property->getAttribute(BelongsTo::class)) {
            return $belongsTo;
        }

        if ($property->hasAttribute(Virtual::class)) {
            return null;
        }

        if (! $property->getType()->isRelation()) {
            return null;
        }

        if ($property->hasAttribute(SerializeWith::class) || $property->getType()->asClass()->haSAttribute(SerializeWith::class)) {
            return null;
        }

        if ($property->hasAttribute(HasOne::class)) {
            return null;
        }

        $belongsTo = new BelongsTo();
        $belongsTo->property = $property;

        return $belongsTo;
    }

    public function getHasOne(string $name): ?HasOne
    {
        if (! $this->isObjectModel()) {
            return null;
        }

        $name = str($name)->camel();

        $singularizedName = $name->singularizeLastWord();

        if (! $singularizedName->equals($name)) {
            return $this->getHasOne($singularizedName);
        }

        if (! $this->modelClass->hasProperty($name)) {
            return null;
        }

        $property = $this->modelClass->getProperty($name);

        if ($hasOne = $property->getAttribute(HasOne::class)) {
            return $hasOne;
        }

        return null;
    }

    public function getHasMany(string $name): ?HasMany
    {
        if (! $this->isObjectModel()) {
            return null;
        }

        $name = str($name)->camel();

        if (! $this->modelClass->hasProperty($name)) {
            return null;
        }

        $property = $this->modelClass->getProperty($name);

        if ($hasMany = $property->getAttribute(HasMany::class)) {
            return $hasMany;
        }

        if ($property->hasAttribute(Virtual::class)) {
            return null;
        }

        if (! $property->getIterableType()?->isRelation()) {
            return null;
        }

        $hasMany = new HasMany();
        $hasMany->property = $property;

        return $hasMany;
    }

    public function isRelation(string|PropertyReflector $name): bool
    {
        $name = ($name instanceof PropertyReflector) ? $name->getName() : $name;

        return $this->getBelongsTo($name) !== null || $this->getHasOne($name) !== null || $this->getHasMany($name) !== null;
    }

    public function getRelation(string|PropertyReflector $name): ?Relation
    {
        $name = ($name instanceof PropertyReflector) ? $name->getName() : $name;

        return $this->getBelongsTo($name) ?? $this->getHasOne($name) ?? $this->getHasMany($name);
    }

    public function getSelectFields(): ImmutableArray
    {
        if (! $this->isObjectModel()) {
            return arr();
        }

        $selectFields = arr();

        foreach ($this->modelClass->getPublicProperties() as $property) {
            $relation = $this->getRelation($property->getName());

            if ($relation instanceof HasMany || $relation instanceof HasOne) {
                continue;
            }

            if ($property->hasAttribute(Virtual::class)) {
                continue;
            }

            if ($relation instanceof BelongsTo) {
                $selectFields[] = $relation->getOwnerFieldName();
            } else {
                $selectFields[] = $property->getName();
            }
        }

        return $selectFields;
    }

    public function resolveRelations(string $relationString, string $parent = ''): array
    {
        if ($relationString === '') {
            return [];
        }

        $relationNames = explode('.', $relationString);

        $currentRelationName = $relationNames[0];

        $currentRelation = $this->getRelation($currentRelationName);

        if ($currentRelation === null) {
            return [];
        }

        unset($relationNames[0]);

        $relationModel = model($currentRelation);

        $newRelationString = implode('.', $relationNames);
        $currentRelation->setParent($parent);
        $newParent = ltrim(sprintf(
            '%s.%s',
            $parent,
            $currentRelationName,
        ), '.');

        $relations = [$currentRelationName => $currentRelation];

        return [...$relations, ...$relationModel->resolveRelations($newRelationString, $newParent)];
    }

    public function resolveEagerRelations(string $parent = ''): array
    {
        if (! $this->isObjectModel()) {
            return [];
        }

        $relations = [];

        foreach ($this->modelClass->getPublicProperties() as $property) {
            if (! $property->hasAttribute(Eager::class)) {
                continue;
            }

            $currentRelationName = $property->getName();
            $currentRelation = $this->getRelation($currentRelationName);

            if (! $currentRelation) {
                continue;
            }

            $relations[$property->getName()] = $currentRelation->setParent($parent);
            $newParent = ltrim(sprintf(
                '%s.%s',
                $parent,
                $currentRelationName,
            ), '.');

            foreach (model($currentRelation)->resolveEagerRelations($newParent) as $name => $nestedEagerRelation) {
                $relations[$name] = $nestedEagerRelation;
            }
        }

        return array_filter($relations);
    }

    public function validate(mixed ...$data): void
    {
        if (! $this->isObjectModel()) {
            return;
        }

        $validator = new Validator();
        $failingRules = [];

        foreach ($data as $key => $value) {
            $property = $this->modelClass->getProperty($key);

            if ($property->hasAttribute(SkipValidation::class)) {
                continue;
            }

            $failingRulesForProperty = $validator->validateValueForProperty(
                $property,
                $value,
            );

            if ($failingRulesForProperty !== []) {
                $failingRules[$key] = $failingRulesForProperty;
            }
        }

        if ($failingRules !== []) {
            throw new ValidationException($this->modelClass->getName(), $failingRules);
        }
    }

    public function getName(): string
    {
        if ($this->isObjectModel()) {
            return $this->modelClass->getName();
        }

        return $this->modelClass;
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getPrimaryField(): string
    {
        return $this->getTableDefinition()->name . '.' . $this->getPrimaryKey();
    }
}
