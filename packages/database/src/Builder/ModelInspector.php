<?php

namespace Tempest\Database\Builder;

use ReflectionException;
use Tempest\Database\BelongsTo;
use Tempest\Database\BelongsToMany;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Eager;
use Tempest\Database\HasMany;
use Tempest\Database\HasManyThrough;
use Tempest\Database\HasOne;
use Tempest\Database\HasOneThrough;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Relation;
use Tempest\Database\Table;
use Tempest\Database\Uuid;
use Tempest\Database\Virtual;
use Tempest\Mapper\Hidden;
use Tempest\Mapper\SerializeAs;
use Tempest\Mapper\SerializeWith;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Memoization\HasMemoization;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\SkipValidation;
use Tempest\Validation\Validator;

use function Tempest\Container\get;
use function Tempest\Database\inspect;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class ModelInspector
{
    use HasMemoization;

    private static array $inspectors = [];

    private(set) ?ClassReflector $reflector = null;

    private(set) object|string|null $instance = null;

    private Validator $validator {
        get => get(Validator::class);
    }

    public static function reset(): void
    {
        self::$inspectors = [];
    }

    public static function forModel(object|string $model): self
    {
        $key = match (true) {
            is_string($model) => $model,
            $model instanceof HasMany => $model->property->getIterableType()->getName(),
            $model instanceof HasManyThrough => $model->property->getIterableType()->getName(),
            $model instanceof BelongsToMany => $model->property->getIterableType()->getName(),
            $model instanceof BelongsTo => $model->property->getType()->getName(),
            $model instanceof HasOne => $model->property->getType()->getName(),
            $model instanceof HasOneThrough => $model->property->getType()->getName(),
            $model instanceof ClassReflector => $model->getName(),
            default => null,
        };

        if ($key === null) {
            return new self($model);
        }

        return self::$inspectors[$key] ??= new self($model);
    }

    public function __construct(
        private(set) object|string $model,
    ) {
        if ($model instanceof HasMany || $model instanceof HasManyThrough || $model instanceof BelongsToMany) {
            $model = $model->property->getIterableType()->asClass();
            $this->reflector = $model;
        } elseif ($model instanceof BelongsTo || $model instanceof HasOne || $model instanceof HasOneThrough) {
            $model = $model->property->getType()->asClass();
            $this->reflector = $model;
        } elseif ($model instanceof ClassReflector) {
            $this->reflector = $model;
        } else {
            try {
                $this->reflector = new ClassReflector($model);
                $this->instance = $model;
            } catch (ReflectionException) {
                $this->reflector = null;
            }
        }
    }

    public function isObjectModel(): bool
    {
        return $this->reflector instanceof ClassReflector;
    }

    public function getTableDefinition(): TableDefinition
    {
        return $this->memoize('getTableDefinition', function () {
            if (! $this->isObjectModel()) {
                return new TableDefinition($this->model);
            }

            $specificName = $this->reflector
                ->getAttribute(Table::class)
                ?->name;

            $conventionalName = get(DatabaseConfig::class)
                ->namingStrategy
                ->getName($this->reflector->getName());

            return new TableDefinition($specificName ?? $conventionalName);
        });
    }

    public function getFieldDefinition(string $field): FieldDefinition
    {
        return $this->memoize('getFieldDefinition' . $field, fn () => new FieldDefinition(
            $this->getTableDefinition(),
            $field,
        ));
    }

    public function getTableName(): string
    {
        return $this->getTableDefinition()->name;
    }

    public function getPropertyValues(): array
    {
        return $this->memoize('getPropertyValues', function () {
            if (! $this->isObjectModel()) {
                return [];
            }

            if (! is_object($this->instance)) {
                return [];
            }

            $values = [];

            foreach ($this->reflector->getProperties() as $property) {
                if ($property->isVirtual()) {
                    continue;
                }

                if ($property->hasAttribute(Virtual::class)) {
                    continue;
                }

                if ($property->getGetHook() === null && ! $property->isInitialized($this->instance)) {
                    continue;
                }

                if ($this->getHasMany($property->getName()) instanceof HasMany) {
                    continue;
                }

                if ($this->getHasOne($property->getName()) instanceof HasOne) {
                    continue;
                }

                if ($this->getHasOneThrough($property->getName()) instanceof HasOneThrough) {
                    continue;
                }

                if ($this->getHasManyThrough($property->getName()) instanceof HasManyThrough) {
                    continue;
                }

                if ($this->getBelongsToMany($property->getName()) instanceof BelongsToMany) {
                    continue;
                }

                $name = $property->getName();

                $values[$name] = $property->getValue($this->instance);
            }

            return $values;
        });
    }

    public function getBelongsTo(string $name): ?BelongsTo
    {
        return $this->memoize('getBelongsTo' . $name, function () use ($name) {
            if (! $this->isObjectModel()) {
                return null;
            }

            $name = str($name);

            $singularizedName = $name->singularizeLastWord();

            if (! $singularizedName->equals($name) && $this->reflector->hasProperty($singularizedName)) {
                return $this->getBelongsTo($singularizedName);
            }

            if (! $this->reflector->hasProperty($name)) {
                $name = $name->camel();

                if (! $this->reflector->hasProperty($name)) {
                    return null;
                }
            }

            $property = $this->reflector->getProperty($name);

            if (($belongsTo = $property->getAttribute(BelongsTo::class)) instanceof BelongsTo) {
                return $belongsTo;
            }

            if ($property->hasAttribute(Virtual::class)) {
                return null;
            }

            if (! $property->getType()->isRelation()) {
                return null;
            }

            if ($property->hasAttribute(SerializeWith::class) || $property->getType()->asClass()->hasAttribute(SerializeWith::class)) {
                return null;
            }

            if ($property->getType()->asClass()->hasAttribute(SerializeAs::class)) {
                return null;
            }

            if ($property->hasAttribute(HasOne::class)) {
                return null;
            }

            if ($property->hasAttribute(HasOneThrough::class)) {
                return null;
            }

            if ($property->hasAttribute(HasManyThrough::class)) {
                return null;
            }

            if ($property->hasAttribute(BelongsToMany::class)) {
                return null;
            }

            $belongsTo = new BelongsTo();
            $belongsTo->property = $property;

            return $belongsTo;
        });
    }

    public function getHasOne(string $name): ?HasOne
    {
        return $this->memoize('getHasOne' . $name, function () use ($name) {
            if (! $this->isObjectModel()) {
                return null;
            }

            $name = str($name);

            $singularizedName = $name->singularizeLastWord();

            if (! $singularizedName->equals($name) && $this->reflector->hasProperty($singularizedName)) {
                return $this->getHasOne($singularizedName);
            }

            if (! $this->reflector->hasProperty($name)) {
                $name = $name->camel();

                if (! $this->reflector->hasProperty($name)) {
                    return null;
                }
            }

            $property = $this->reflector->getProperty($name);

            if (($hasOne = $property->getAttribute(HasOne::class)) instanceof HasOne) {
                return $hasOne;
            }

            return null;
        });
    }

    public function getHasMany(string $name): ?HasMany
    {
        return $this->memoize('getHasMany' . $name, function () use ($name) {
            if (! $this->isObjectModel()) {
                return null;
            }

            $name = str($name);

            if (! $this->reflector->hasProperty($name)) {
                $name = $name->camel();

                if (! $this->reflector->hasProperty($name)) {
                    return null;
                }
            }

            $property = $this->reflector->getProperty($name);

            if (($hasMany = $property->getAttribute(HasMany::class)) instanceof HasMany) {
                return $hasMany;
            }

            if ($property->hasAttribute(HasManyThrough::class)) {
                return null;
            }

            if ($property->hasAttribute(BelongsToMany::class)) {
                return null;
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
        });
    }

    public function getHasOneThrough(string $name): ?HasOneThrough
    {
        return $this->memoize('getHasOneThrough' . $name, function () use ($name) {
            if (! $this->isObjectModel()) {
                return null;
            }

            $name = str($name);

            $singularizedName = $name->singularizeLastWord();

            if (! $singularizedName->equals($name) && $this->reflector->hasProperty($singularizedName)) {
                return $this->getHasOneThrough($singularizedName);
            }

            if (! $this->reflector->hasProperty($name)) {
                $name = $name->camel();

                if (! $this->reflector->hasProperty($name)) {
                    return null;
                }
            }

            $property = $this->reflector->getProperty($name);

            if (($hasOneThrough = $property->getAttribute(HasOneThrough::class)) instanceof HasOneThrough) {
                return $hasOneThrough;
            }

            return null;
        });
    }

    public function getHasManyThrough(string $name): ?HasManyThrough
    {
        return $this->memoize('getHasManyThrough' . $name, function () use ($name) {
            if (! $this->isObjectModel()) {
                return null;
            }

            $name = str($name);

            if (! $this->reflector->hasProperty($name)) {
                $name = $name->camel();

                if (! $this->reflector->hasProperty($name)) {
                    return null;
                }
            }

            $property = $this->reflector->getProperty($name);

            if (($hasManyThrough = $property->getAttribute(HasManyThrough::class)) instanceof HasManyThrough) {
                return $hasManyThrough;
            }

            return null;
        });
    }

    public function getBelongsToMany(string $name): ?BelongsToMany
    {
        return $this->memoize('getBelongsToMany' . $name, function () use ($name) {
            if (! $this->isObjectModel()) {
                return null;
            }

            $name = str($name);

            if (! $this->reflector->hasProperty($name)) {
                $name = $name->camel();

                if (! $this->reflector->hasProperty($name)) {
                    return null;
                }
            }

            $property = $this->reflector->getProperty($name);

            if (($belongsToMany = $property->getAttribute(BelongsToMany::class)) instanceof BelongsToMany) {
                return $belongsToMany;
            }

            return null;
        });
    }

    public function isRelation(string|PropertyReflector $name): bool
    {
        $name = $name instanceof PropertyReflector ? $name->getName() : $name;

        return $this->memoize(
            'isRelation' . $name,
            fn () => (
                $this->getBelongsTo($name) instanceof BelongsTo
                || $this->getHasOne($name) instanceof HasOne
                || $this->getHasMany($name) instanceof HasMany
                || $this->getHasOneThrough($name) instanceof HasOneThrough
                || $this->getHasManyThrough($name) instanceof HasManyThrough
                || $this->getBelongsToMany($name) instanceof BelongsToMany
            ),
        );
    }

    public function getRelation(string|PropertyReflector $name): ?Relation
    {
        $name = $name instanceof PropertyReflector ? $name->getName() : $name;

        return $this->memoize(
            'getRelation' . $name,
            fn () => (
                $this->getBelongsTo($name) ?? $this->getHasOne($name) ?? $this->getHasMany($name) ?? $this->getHasOneThrough($name) ?? $this->getHasManyThrough(
                    $name,
                ) ?? $this->getBelongsToMany($name)
            ),
        );
    }

    /**
     * @return \Tempest\Support\Arr\ImmutableArray<array-key, Relation>
     */
    public function getRelations(): ImmutableArray
    {
        return $this->memoize('getRelations', function () {
            if (! $this->isObjectModel()) {
                return arr();
            }

            $relationFields = arr();

            foreach ($this->reflector->getPublicProperties() as $property) {
                if (! ($relation = $this->getRelation($property->getName())) instanceof Relation) {
                    continue;
                }

                $relationFields[] = $relation;
            }

            return $relationFields;
        });
    }

    /**
     * @return \Tempest\Support\Arr\ImmutableArray<array-key, PropertyReflector>
     */
    public function getValueFields(): ImmutableArray
    {
        if (! $this->isObjectModel()) {
            return arr();
        }

        $valueFields = arr();

        foreach ($this->reflector->getPublicProperties() as $property) {
            if ($property->isVirtual()) {
                continue;
            }

            if ($property->hasAttribute(Virtual::class)) {
                continue;
            }

            if ($this->isRelation($property->getName())) {
                continue;
            }

            $valueFields[] = $property;
        }

        return $valueFields;
    }

    public function isRelationLoaded(string|PropertyReflector|Relation $relation): bool
    {
        if (! $this->isObjectModel()) {
            return false;
        }

        if (! $relation instanceof Relation) {
            $relation = $this->getRelation($relation);
        }

        if (! $relation instanceof Relation) {
            return false;
        }

        if (! $relation->property->isInitialized($this->instance)) {
            return false;
        }

        return $relation->property->getValue($this->instance) !== null;
    }

    public function getSelectFields(): ImmutableArray
    {
        if (! $this->isObjectModel()) {
            return arr();
        }

        $selectFields = arr();

        if (($primaryKey = $this->getPrimaryKeyProperty()) instanceof PropertyReflector) {
            $selectFields[] = $primaryKey->getName();
        }

        foreach ($this->reflector->getPublicProperties() as $property) {
            $relation = $this->getRelation($property->getName());
            if ($relation instanceof HasMany) {
                continue;
            }

            if ($relation instanceof HasOne) {
                continue;
            }

            if ($relation instanceof HasOneThrough) {
                continue;
            }

            if ($relation instanceof HasManyThrough) {
                continue;
            }

            if ($relation instanceof BelongsToMany) {
                continue;
            }

            if ($property->isVirtual()) {
                continue;
            }

            if ($property->hasAttribute(Virtual::class)) {
                continue;
            }

            if ($property->hasAttribute(Hidden::class)) {
                continue;
            }

            if ($property->getType()->equals(PrimaryKey::class)) {
                continue;
            }

            if ($relation instanceof BelongsTo) {
                $selectFields[] = $relation->getOwnerFieldName();
            } else {
                $selectFields[] = $property->getName();
            }
        }

        return $selectFields->unique();
    }

    public function resolveRelations(string $relationString, string $parent = '', array $visitedPaths = []): array
    {
        if ($relationString === '') {
            return [];
        }

        $relationNames = explode('.', $relationString);

        $currentRelationName = $relationNames[0];

        $currentRelation = $this->getRelation($currentRelationName);

        if (! $currentRelation instanceof Relation) {
            return [];
        }

        unset($relationNames[0]);

        $relationModel = inspect($currentRelation);
        $modelType = $relationModel->getName();

        $fullPath = $parent !== ''
            ? "{$parent}.{$currentRelationName}"
            : $currentRelationName;

        if (in_array($modelType, $visitedPaths, true)) {
            return [$fullPath => $currentRelation->setParent($parent)];
        }

        $newRelationString = implode('.', $relationNames);
        $currentRelation->setParent($parent);
        $newParent = ltrim(sprintf(
            '%s.%s',
            $parent,
            $currentRelationName,
        ), '.');

        $relations = [$fullPath => $currentRelation];

        return [
            ...$relations,
            ...$relationModel->resolveRelations($newRelationString, $newParent, [...$visitedPaths, $this->getName()]),
        ];
    }

    public function resolveEagerRelations(string $parent = '', array $visitedPaths = []): array
    {
        if (! $this->isObjectModel()) {
            return [];
        }

        $relations = [];

        foreach ($this->reflector->getPublicProperties() as $property) {
            if (! $property->hasAttribute(Eager::class)) {
                continue;
            }

            $currentRelationName = $property->getName();
            $currentRelation = $this->getRelation($currentRelationName);

            if (! $currentRelation instanceof Relation) {
                continue;
            }

            $newParent = ltrim(sprintf(
                '%s.%s',
                $parent,
                $currentRelationName,
            ), '.');

            $relationModel = inspect($currentRelation);
            $modelType = $relationModel->getName();

            if (in_array($modelType, $visitedPaths, true)) {
                continue;
            }

            $fullPath = $parent !== ''
                ? "{$parent}.{$currentRelationName}"
                : $currentRelationName;
            $relations[$fullPath] = $currentRelation->setParent($parent);
            $newVisitedPaths = [...$visitedPaths, $this->getName()];

            foreach ($relationModel->resolveEagerRelations($newParent, $newVisitedPaths) as $name => $nestedEagerRelation) {
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

        $failingRules = [];

        foreach ($data as $key => $value) {
            $property = $this->reflector->getProperty($key);

            if ($property->hasAttribute(SkipValidation::class)) {
                continue;
            }

            if ($property->getType()->getName() === PrimaryKey::class) {
                continue;
            }

            $failingRulesForProperty = $this->validator->validateValueForProperty(
                $property,
                $value,
            );

            if ($failingRulesForProperty !== []) {
                $failingRules[$key] = $failingRulesForProperty;
            }
        }

        if ($failingRules !== []) {
            throw new ValidationFailed($failingRules, $this->reflector->getName());
        }
    }

    public function getName(): string
    {
        if ($this->reflector instanceof ClassReflector) {
            return $this->reflector->getName();
        }

        return $this->model;
    }

    public function getQualifiedPrimaryKey(): ?string
    {
        $primaryKey = $this->getPrimaryKey();

        return $primaryKey !== null
            ? $this->getTableDefinition()->name . '.' . $primaryKey
            : null;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->getPrimaryKeyProperty()?->getName();
    }

    public function hasPrimaryKey(): bool
    {
        return $this->getPrimaryKeyProperty() instanceof PropertyReflector;
    }

    public function getPrimaryKeyProperty(): ?PropertyReflector
    {
        if (! $this->isObjectModel()) {
            return null;
        }

        $primaryKeys = arr($this->reflector->getProperties())
            ->filter(fn (PropertyReflector $property) => $property->getType()->matches(PrimaryKey::class));

        return match ($primaryKeys->count()) {
            0 => null,
            default => $primaryKeys->first(),
        };
    }

    public function getPrimaryKeyValue(): ?PrimaryKey
    {
        if (! $this->isObjectModel()) {
            return null;
        }

        if (! is_object($this->instance)) {
            return null;
        }

        $primaryKeyProperty = $this->getPrimaryKeyProperty();

        if (! $primaryKeyProperty instanceof PropertyReflector) {
            return null;
        }

        if (! $primaryKeyProperty->isInitialized($this->instance)) {
            return null;
        }

        return $primaryKeyProperty->getValue($this->instance);
    }

    public function hasUuidPrimaryKey(): bool
    {
        return $this->getPrimaryKeyProperty()?->hasAttribute(Uuid::class) ?? false;
    }

    public function isUuidPrimaryKey(PropertyReflector $property): bool
    {
        return $property->getType()->matches(PrimaryKey::class) && $property->hasAttribute(Uuid::class);
    }
}
