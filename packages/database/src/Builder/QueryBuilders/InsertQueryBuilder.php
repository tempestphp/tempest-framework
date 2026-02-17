<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Database;
use Tempest\Database\DatabaseContext;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeInsterted;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeInserted;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\HasOne;
use Tempest\Database\OnDatabase;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\InsertStatement;
use Tempest\Database\Virtual;
use Tempest\Intl;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Random;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Container\get;
use function Tempest\Database\inspect;
use function Tempest\Support\str;

/**
 * @template TModel of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModel>
 */
final class InsertQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, TransformsQueryBuilder;

    private InsertStatement $insert;

    private array $after = [];

    public array $bindings = [];

    public ModelInspector $model;

    private Database $database {
        get => get(Database::class, $this->onDatabase);
    }

    private DatabaseContext $context {
        get => new DatabaseContext(dialect: $this->database->dialect);
    }

    /**
     * @param class-string<TModel>|string|TModel $model
     */
    public function __construct(
        string|object $model,
        private readonly array $rows,
        private readonly SerializerFactory $serializerFactory,
    ) {
        $this->model = inspect($model);
        $this->insert = new InsertStatement($this->model->getTableDefinition());
    }

    /**
     * Executes the insert query and returns the primary key of the inserted record.
     */
    public function execute(mixed ...$bindings): ?PrimaryKey
    {
        $id = $this->build()->execute(...$bindings);

        if ($id === null) {
            return null;
        }

        foreach ($this->after as $after) {
            $query = $after($id);

            if ($query instanceof BuildsQuery) {
                $query->build()->execute();
            }
        }

        return $id;
    }

    /**
     * Compile the query to a SQL statement without the bindings.
     */
    public function compile(): ImmutableString
    {
        return $this->build()->compile();
    }

    /**
     * Returns the SQL statement with bindings. This method may generate syntax errors, it is not recommended to use it other than for debugging.
     */
    public function toRawSql(): ImmutableString
    {
        return $this->build()->toRawSql();
    }

    public function build(mixed ...$bindings): Query
    {
        foreach ($this->resolveData() as $data) {
            foreach ($data as $value) {
                $bindings[] = $value;
            }

            $this->insert->addEntry($data);
        }

        return new Query(
            sql: $this->insert,
            bindings: [...$this->bindings, ...$bindings],
            primaryKeyColumn: $this->model->getPrimaryKey(),
        )->onDatabase($this->onDatabase);
    }

    /**
     * Binds the provided values to the query, allowing for parameterized queries.
     */
    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    /**
     * Registers callbacks to be executed after the insert operation completes.
     */
    public function then(Closure ...$callbacks): self
    {
        $this->after = [...$this->after, ...$callbacks];

        return $this;
    }

    private function removeTablePrefix(string $columnName): string
    {
        return str($columnName)->contains('.')
            ? str($columnName)->afterLast('.')->toString()
            : $columnName;
    }

    private function getDefaultForeignKeyName(): string
    {
        return Intl\singularize($this->model->getTableName()) . '_' . $this->model->getPrimaryKey();
    }

    private function convertObjectToArray(object $object, array $excludeProperties = []): array
    {
        $reflection = new ClassReflector($object);
        $data = [];

        foreach ($reflection->getPublicProperties() as $property) {
            if ($property->isUninitialized($object)) {
                continue;
            }

            if ($property->isVirtual()) {
                continue;
            }

            $propertyName = $property->getName();

            if (! in_array($propertyName, $excludeProperties, strict: true)) {
                $data[$propertyName] = $property->getValue($object);
            }
        }

        return $data;
    }

    private function prepareRelationItem(mixed $item, string $foreignKey, PrimaryKey $parentId): array
    {
        if (is_array($item)) {
            $item[$foreignKey] = $parentId;
            return $item;
        }

        if (! is_object($item)) {
            return [];
        }

        $foreignKeyProperty = str($foreignKey)->before('_')->toString();

        if (property_exists($item, $foreignKey)) {
            $item->{$foreignKey} = $parentId;
            return $this->convertObjectToArray($item);
        }

        if (property_exists($item, $foreignKeyProperty)) {
            $data = $this->convertObjectToArray($item, [$foreignKeyProperty]);
            $data[$foreignKey] = $parentId;
            return $data;
        }

        $data = $this->convertObjectToArray($item);
        $data[$foreignKey] = $parentId;

        return $data;
    }

    private function addHasManyRelationCallback(string $relationName, array $relations): void
    {
        $hasMany = $this->model->getHasMany($relationName);

        if ($hasMany === null) {
            return;
        }

        if (! $this->model->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($this->model->getName(), 'HasMany');
        }

        $this->after[] = function (PrimaryKey $parentId) use ($hasMany, $relations) {
            $foreignKey = $hasMany->ownerJoin
                ? $this->removeTablePrefix($hasMany->ownerJoin)
                : $this->getDefaultForeignKeyName();

            $insert = Arr\map(
                array: $relations,
                map: fn ($item) => $this->prepareRelationItem($item, $foreignKey, $parentId),
            );

            if ($insert === []) {
                return null;
            }

            return new InsertQueryBuilder(
                model: $hasMany->property->getIterableType()->asClass(),
                rows: $insert,
                serializerFactory: $this->serializerFactory,
            );
        };
    }

    private function addHasOneRelationCallback(string $relationName, object|iterable $relation): void
    {
        $hasOne = $this->model->getHasOne($relationName);

        if ($hasOne === null) {
            return;
        }

        $this->after[] = function (PrimaryKey $parentId) use ($hasOne, $relation) {
            if ($hasOne->ownerJoin) {
                return $this->handleCustomHasOneRelation($hasOne, $relation, $parentId);
            }

            return $this->handleStandardHasOneRelation($hasOne, $relation, $parentId);
        };
    }

    private function handleCustomHasOneRelation(HasOne $hasOne, object|array $relation, PrimaryKey $parentId): null
    {
        $relatedModelId = new InsertQueryBuilder(
            model: $hasOne->property->getType()->asClass(),
            rows: [$relation],
            serializerFactory: $this->serializerFactory,
        )->execute();

        $ownerModel = inspect($this->model->getName());
        $foreignKeyColumn = $hasOne->relationJoin ?? $this->removeTablePrefix($hasOne->ownerJoin);

        $updateQuery = sprintf(
            'UPDATE %s SET %s = ? WHERE %s = ?',
            $ownerModel->getTableName(),
            $foreignKeyColumn,
            $ownerModel->getPrimaryKey(),
        );

        $query = new Query($updateQuery, [$relatedModelId->value, $parentId->value]);
        $query->onDatabase($this->onDatabase)->execute();

        return null;
    }

    private function handleStandardHasOneRelation(HasOne $hasOne, object|array $relation, PrimaryKey $parentId): ?PrimaryKey
    {
        $ownerModel = inspect($this->model->getName());

        if (! $ownerModel->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($ownerModel->getName(), 'HasOne');
        }

        // TODO: we might need to bake this into the naming strategy class
        $foreignKeyColumn = Intl\singularize_last_word($ownerModel->getTableName()) . '_' . $ownerModel->getPrimaryKey();

        $preparedData = is_array($relation)
            ? [...$relation, ...[$foreignKeyColumn => $parentId->value]]
            : [...$this->convertObjectToArray($relation), ...[$foreignKeyColumn => $parentId->value]];

        $relatedModelQuery = new InsertQueryBuilder(
            model: $hasOne->property->getType()->asClass(),
            rows: [$preparedData],
            serializerFactory: $this->serializerFactory,
        );

        return $relatedModelQuery->execute();
    }

    private function resolveData(): array
    {
        return Arr\map(
            array: $this->rows,
            map: fn (object|iterable $model) => $this->resolveModelData($model),
        );
    }

    private function resolveModelData(object|iterable $model): array
    {
        return is_iterable($model)
            ? $this->resolveIterableData($model)
            : $this->resolveObjectData($model);
    }

    private function resolveIterableData(iterable $model): array
    {
        $entry = [];

        foreach ($model as $key => $value) {
            if ($this->handleHasManyRelation($key, $value)) {
                continue;
            }

            if ($this->handleHasOneRelation($key, $value)) {
                continue;
            }

            if ($this->handleBelongsToRelation($key, $value, $entry)) {
                continue;
            }

            $entry[$key] = $this->serializeIterableValue($key, $value);
        }

        return $entry;
    }

    private function handleHasManyRelation(string $key, mixed $relations): bool
    {
        $hasMany = $this->model->getHasMany($key);

        if ($hasMany === null) {
            return false;
        }

        if (! is_iterable($relations)) {
            throw new HasManyRelationCouldNotBeInsterted($this->model->getName(), $key);
        }

        $this->addHasManyRelationCallback($key, $relations);

        return true;
    }

    private function handleHasOneRelation(string $key, mixed $relation): bool
    {
        $hasOne = $this->model->getHasOne($key);

        if ($hasOne === null) {
            return false;
        }

        if (! is_object($relation) && ! is_array($relation)) {
            throw new HasOneRelationCouldNotBeInserted($this->model->getName(), $key);
        }

        $this->addHasOneRelationCallback($key, $relation);

        return true;
    }

    private function handleBelongsToRelation(string $key, mixed $value, array &$entry): bool
    {
        $belongsTo = $this->model->getBelongsTo($key);

        if ($belongsTo === null || ! is_object($value) && ! is_array($value)) {
            return false;
        }

        $relatedId = new InsertQueryBuilder(
            model: $belongsTo->property->getType()->asClass(),
            rows: [$value],
            serializerFactory: $this->serializerFactory,
        )->execute();

        $entry[$belongsTo->getOwnerFieldName()] = $relatedId;

        return true;
    }

    private function resolveObjectData(object $model): array
    {
        $definition = inspect($model);
        $modelClass = new ClassReflector($model);
        $entry = [];

        foreach ($modelClass->getPublicProperties() as $property) {
            $propertyName = $property->getName();

            if (! $property->isInitialized($model)) {
                if ($definition->isUuidPrimaryKey($property)) {
                    $uuid = new PrimaryKey(Random\uuid());
                    $property->setValue($model, $uuid);
                    $entry[$propertyName] = $this->serializeValue($property, $uuid);
                }

                continue;
            }

            if ($property->isVirtual()) {
                continue;
            }

            if ($property->hasAttribute(Virtual::class)) {
                continue;
            }

            $value = $property->getValue($model);

            if ($definition->getHasMany($propertyName)) {
                if (is_iterable($value)) {
                    $this->addHasManyRelationCallback($propertyName, $value);
                }

                continue;
            }

            if ($definition->getHasOne($propertyName)) {
                if (is_object($value) || is_array($value)) {
                    $this->addHasOneRelationCallback($propertyName, $value);
                }

                continue;
            }

            $column = $propertyName;

            if ($property->getType()->getName() === PrimaryKey::class && $value === null) {
                continue;
            }

            if ($definition->isRelation($property)) {
                [$column, $value] = $this->resolveRelationProperty($definition, $property, $value);
            } else {
                $value = $this->serializeValue($property, $value);
            }

            $entry[$column] = $value;
        }

        return $entry;
    }

    private function resolveRelationProperty(ModelInspector $definition, PropertyReflector $property, mixed $value): array
    {
        $relationModel = inspect($property->getType()->asClass());
        $primaryKey = $relationModel->getPrimaryKey();

        if (! $primaryKey) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'BelongsTo');
        }

        $belongsTo = $definition->getBelongsTo($property->getName());
        $column = $belongsTo
            ? $belongsTo->getOwnerFieldName()
            : $property->getName() . '_' . $primaryKey;

        $resolvedValue = match (true) {
            $value === null => null,
            isset($value->{$primaryKey}) => $value->{$primaryKey}->value,
            default => new InsertQueryBuilder($value::class, [$value], $this->serializerFactory)->build(),
        };

        return [$column, $resolvedValue];
    }

    private function serializeValue(PropertyReflector $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->serializerFactory
            ->in($this->context)
            ->forProperty($property)
            ?->serialize($value) ?? $value;
    }

    private function serializeIterableValue(string $key, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        // Booleans should be handled by the database layer, not by serializers
        if (is_bool($value)) {
            return $value;
        }

        // Only serialize if we have an object model to work with
        if (! $this->model->isObjectModel()) {
            return $value;
        }

        if (! $this->model->reflector->hasProperty($key)) {
            return $value;
        }

        $property = $this->model->reflector->getProperty($key);

        if ($property->getType()->accepts(PrimaryKey::class)) {
            return $value;
        }

        return $this->serializeValue(
            property: $property,
            value: $value,
        );
    }
}
