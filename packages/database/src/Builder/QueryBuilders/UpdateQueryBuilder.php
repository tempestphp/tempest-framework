<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\Database;
use Tempest\Database\DatabaseContext;
use Tempest\Database\Exceptions\CouldNotUpdateRelation;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeUpdated;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeUpdated;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\OnDatabase;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\UpdateStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Database\Virtual;
use Tempest\Intl;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Container\get;
use function Tempest\Database\inspect;

/**
 * @template TModel of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModel>
 * @implements \Tempest\Database\Builder\QueryBuilders\SupportsWhereStatements<TModel>
 * @use \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<TModel>
 */
final class UpdateQueryBuilder implements BuildsQuery, SupportsWhereStatements
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods, TransformsQueryBuilder;

    private UpdateStatement $update;

    public array $bindings = [];

    public ModelInspector $model;

    private array $after = [];

    private ?PrimaryKey $primaryKeyForRelations = null;

    private Database $database {
        get => get(Database::class, $this->onDatabase);
    }

    private DatabaseContext $context {
        get => new DatabaseContext(dialect: $this->database->dialect);
    }

    public ImmutableArray $wheres {
        get => $this->update->where;
    }

    /**
     * @param class-string<TModel>|string|TModel $model
     */
    public function __construct(
        string|object $model,
        private readonly array|ImmutableArray $values,
        private readonly SerializerFactory $serializerFactory,
    ) {
        $this->model = inspect($model);

        $this->update = new UpdateStatement(
            table: $this->model->getTableDefinition(),
        );
    }

    /**
     * Creates an instance from another query builder, inheriting conditions and bindings.
     *
     * @template TSourceModel of object
     * @param (BuildsQuery<TSourceModel>&SupportsWhereStatements<TSourceModel>) $source
     * @return UpdateQueryBuilder<TSourceModel>
     */
    public static function fromQueryBuilder(BuildsQuery&SupportsWhereStatements $source, mixed ...$values): UpdateQueryBuilder
    {
        $builder = new self($source->model->model, $values, get(SerializerFactory::class));
        $builder->bind(...$source->bindings);

        foreach ($source->wheres as $where) {
            $builder->wheres[] = $where;
        }

        return $builder;
    }

    /**
     * Executes the update query and returns the primary key of the updated record.
     */
    public function execute(mixed ...$bindings): ?PrimaryKey
    {
        // For relation-only updates, we need to resolve values to set up
        // callbacks but we won't actually execute an UPDATE statement.
        if ($this->hasOnlyRelationUpdates()) {
            $this->resolveValuesToUpdate();
            $result = $this->primaryKeyForRelations;
        } else {
            $result = $this->build()->execute(...$bindings);
        }

        // Execute after callbacks for relation updates
        if ($this->model->hasPrimaryKey() && $this->after !== [] && $this->primaryKeyForRelations !== null) {
            foreach ($this->after as $after) {
                $query = $after($this->primaryKeyForRelations);

                if ($query instanceof BuildsQuery) {
                    $query->build()->execute();
                }
            }
        }

        return $result;
    }

    /**
     * Allows the update operation to proceed without WHERE conditions, updating all records.
     *
     * @return self<TModel>
     */
    public function allowAll(): self
    {
        $this->update->allowAll = true;

        return $this;
    }

    /**
     * Binds the provided values to the query, allowing for parameterized queries.
     *
     * @return self<TModel>
     */
    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
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
        $values = $this->resolveValuesToUpdate();

        if ($this->model->hasPrimaryKey()) {
            unset($values[$this->model->getPrimaryKey()]);
        }

        $this->update->values = $values;

        $this->setWhereForObjectModel();

        $allBindings = [];

        foreach ($values as $value) {
            $allBindings[] = $value;
        }

        foreach ($this->bindings as $binding) {
            $allBindings[] = $binding;
        }

        foreach ($bindings as $binding) {
            $allBindings[] = $binding;
        }

        return new Query($this->update, $allBindings)->onDatabase($this->onDatabase);
    }

    private function resolveValuesToUpdate(): ImmutableArray
    {
        if ($this->hasRelationUpdates()) {
            $this->validateRelationUpdateConstraints();
        }

        if (! $this->model->isObjectModel()) {
            return new ImmutableArray($this->values);
        }

        $values = [];
        foreach ($this->values as $column => $value) {
            if ($this->handleRelationUpdate($column, $value)) {
                continue;
            }

            $property = $this->model->reflector->getProperty($column);
            [$resolvedColumn, $resolvedValue] = $this->resolvePropertyValue($property, $column, $value);

            $values[$resolvedColumn] = $resolvedValue;
        }

        return new ImmutableArray($values);
    }

    private function handleRelationUpdate(string $column, mixed $value): bool
    {
        return $this->handleHasManyRelation($column, $value) || $this->handleHasOneRelation($column, $value);
    }

    private function resolvePropertyValue(PropertyReflector $property, string $column, mixed $value): array
    {
        if ($this->model->isRelation($property)) {
            return $this->resolveRelationValue($property, $column, $value);
        }

        $value = $this->serializeValue($property, $value);

        return [$column, $value];
    }

    private function resolveRelationValue(PropertyReflector $property, string $column, mixed $value): array
    {
        $belongsTo = $this->model->getBelongsTo($column);

        if ($belongsTo) {
            $column = $belongsTo->getOwnerFieldName();
            $relationModel = inspect($property->getType()->asClass());
            $this->ensureModelHasPrimaryKey($relationModel, 'BelongsTo');
            $primaryKey = $relationModel->getPrimaryKey();
        } else {
            $relationModel = inspect($property->getType()->asClass());
            $this->ensureModelHasPrimaryKey($relationModel, 'relation');
            $primaryKey = $relationModel->getPrimaryKey();
            $column .= '_' . $primaryKey;
        }

        $resolvedValue = match (true) {
            $value === null => null,
            is_object($value) && isset($value->{$primaryKey}) => $value->{$primaryKey}->value,
            is_object($value) || is_array($value) => new InsertQueryBuilder(
                model: $property->getType()->asClass(),
                rows: [$value],
                serializerFactory: $this->serializerFactory,
            )->build(),
            default => $value,
        };

        return [$column, $resolvedValue];
    }

    private function serializeValue(PropertyReflector $property, mixed $value): mixed
    {
        $serializer = $this->serializerFactory
            ->in($this->context)
            ->forProperty($property);

        if ($value !== null && $serializer !== null) {
            return $serializer->serialize($value);
        }

        return $value;
    }

    private function ensureModelHasPrimaryKey(ModelInspector $model, string $relationType): void
    {
        if (! $model->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($model->getName(), $relationType);
        }
    }

    private function handleHasManyRelation(string $key, mixed $relations): bool
    {
        $hasMany = $this->model->getHasMany($key);

        if ($hasMany === null) {
            return false;
        }

        if (! is_iterable($relations)) {
            throw new HasManyRelationCouldNotBeUpdated($this->model->getName(), $key);
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
            throw new HasOneRelationCouldNotBeUpdated($this->model->getName(), $key);
        }

        $this->addHasOneRelationCallback($key, $relation);

        return true;
    }

    private function addHasManyRelationCallback(string $relationName, iterable $relations): void
    {
        $hasMany = $this->model->getHasMany($relationName);

        if ($hasMany === null) {
            return;
        }

        $this->ensureModelHasPrimaryKey($this->model, 'HasMany');

        $this->after[] = function (PrimaryKey $parentId) use ($hasMany, $relations) {
            $this->deleteExistingHasManyRelations($hasMany, $parentId);

            $foreignKey = $hasMany->ownerJoin
                ? $this->removeTablePrefix($hasMany->ownerJoin)
                : $this->getDefaultForeignKeyName();

            $insert = [];
            foreach ($relations as $item) {
                $insert[] = $this->prepareRelationItem($item, $foreignKey, $parentId);
            }

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

    private function addHasOneRelationCallback(string $relationName, object|array $relation): void
    {
        $hasOne = $this->model->getHasOne($relationName);

        if ($hasOne === null) {
            return;
        }

        $this->after[] = function (PrimaryKey $parentId) use ($hasOne, $relation) {
            $this->deleteExistingHasOneRelation($hasOne, $parentId);

            if ($hasOne->ownerJoin) {
                return $this->handleCustomHasOneRelation($hasOne, $relation, $parentId);
            }

            return $this->handleStandardHasOneRelation($hasOne, $relation, $parentId);
        };
    }

    private function deleteExistingHasManyRelations(mixed $hasMany, PrimaryKey $parentId): void
    {
        $relatedModel = inspect($hasMany->property->getIterableType()->asClass());
        $foreignKey = $hasMany->ownerJoin
            ? $this->removeTablePrefix($hasMany->ownerJoin)
            : $this->getDefaultForeignKeyName();

        new DeleteQueryBuilder($relatedModel->getName())
            ->whereField($foreignKey, $parentId->value)
            ->build()
            ->onDatabase($this->onDatabase)
            ->execute();
    }

    private function deleteExistingHasOneRelation(mixed $hasOne, PrimaryKey $parentId): void
    {
        if ($hasOne->ownerJoin) {
            $this->deleteCustomHasOneRelation($hasOne, $parentId);
            return;
        }

        $this->deleteStandardHasOneRelation($hasOne, $parentId);
    }

    private function deleteCustomHasOneRelation(mixed $hasOne, PrimaryKey $parentId): void
    {
        $ownerModel = inspect($this->model->getName());
        $relatedModel = inspect($hasOne->property->getType()->asClass());

        $this->ensureModelHasPrimaryKey($ownerModel, 'HasOne');
        $this->ensureModelHasPrimaryKey($relatedModel, 'HasOne');

        $foreignKeyColumn = $hasOne->relationJoin ?? $this->removeTablePrefix($hasOne->ownerJoin);

        $result = new SelectQueryBuilder($ownerModel->getName(), new ImmutableArray([$foreignKeyColumn]))
            ->whereField($ownerModel->getPrimaryKey(), $parentId->value)
            ->build()
            ->onDatabase($this->onDatabase)
            ->fetchFirst();

        if (! $result || ! isset($result[$foreignKeyColumn])) {
            return;
        }

        $relatedId = $result[$foreignKeyColumn];

        new DeleteQueryBuilder($relatedModel->getName())
            ->whereField($relatedModel->getPrimaryKey(), $relatedId)
            ->build()
            ->onDatabase($this->onDatabase)
            ->execute();

        new UpdateQueryBuilder($ownerModel->getName(), [$foreignKeyColumn => null], $this->serializerFactory)
            ->whereField($ownerModel->getPrimaryKey(), $parentId->value)
            ->build()
            ->onDatabase($this->onDatabase)
            ->execute();
    }

    private function deleteStandardHasOneRelation(mixed $hasOne, PrimaryKey $parentId): void
    {
        $ownerModel = inspect($this->model->getName());
        $relatedModel = inspect($hasOne->property->getType()->asClass());

        $this->ensureModelHasPrimaryKey($ownerModel, 'HasOne');

        $foreignKeyColumn = Intl\singularize($ownerModel->getTableName()) . '_' . $ownerModel->getPrimaryKey();

        new DeleteQueryBuilder($relatedModel->getName())
            ->whereField($foreignKeyColumn, $parentId->value)
            ->build()
            ->onDatabase($this->onDatabase)
            ->execute();
    }

    private function handleCustomHasOneRelation(mixed $hasOne, object|array $relation, PrimaryKey $parentId): null
    {
        $relatedModelId = new InsertQueryBuilder(
            model: $hasOne->property->getType()->asClass(),
            rows: [$relation],
            serializerFactory: $this->serializerFactory,
        )->execute();

        $ownerModel = inspect($this->model->getName());
        $this->ensureModelHasPrimaryKey($ownerModel, 'HasOne');

        $foreignKeyColumn = $hasOne->relationJoin ?? $this->removeTablePrefix($hasOne->ownerJoin);

        new UpdateQueryBuilder($ownerModel->getName(), [$foreignKeyColumn => $relatedModelId->value], $this->serializerFactory)
            ->whereField($ownerModel->getPrimaryKey(), $parentId->value)
            ->build()
            ->onDatabase($this->onDatabase)
            ->execute();

        return null;
    }

    private function handleStandardHasOneRelation(mixed $hasOne, object|array $relation, PrimaryKey $parentId): ?PrimaryKey
    {
        $ownerModel = inspect($this->model->getName());
        $this->ensureModelHasPrimaryKey($ownerModel, 'HasOne');

        $foreignKeyColumn = Intl\singularize($ownerModel->getTableName()) . '_' . $ownerModel->getPrimaryKey();

        $preparedData = is_array($relation)
            ? [...$relation, $foreignKeyColumn => $parentId->value]
            : [...$this->convertObjectToArray($relation), $foreignKeyColumn => $parentId->value];

        return new InsertQueryBuilder(
            model: $hasOne->property->getType()->asClass(),
            rows: [$preparedData],
            serializerFactory: $this->serializerFactory,
        )->execute();
    }

    private function prepareRelationItem(object|array $item, string $foreignKey, PrimaryKey $parentId): array
    {
        $preparedData = is_array($item)
            ? $item
            : $this->convertObjectToArray($item);

        $preparedData[$foreignKey] = $parentId->value;

        return $preparedData;
    }

    private function convertObjectToArray(object $object): array
    {
        $result = [];
        $reflection = new ClassReflector($object);

        foreach ($reflection->getPublicProperties() as $property) {
            if ($property->hasAttribute(Virtual::class)) {
                continue;
            }

            if ($property->isInitialized($object)) {
                $result[$property->getName()] = $property->getValue($object);
            }
        }

        return $result;
    }

    private function getDefaultForeignKeyName(): string
    {
        $this->ensureModelHasPrimaryKey($this->model, 'relation');

        return Intl\singularize($this->model->getTableName()) . '_' . $this->model->getPrimaryKey();
    }

    private function removeTablePrefix(string $column): string
    {
        $tableName = $this->model->getTableName();
        $prefix = $tableName . '.';

        if (str_starts_with($column, $prefix)) {
            return substr($column, strlen($prefix));
        }

        return $column;
    }

    /**
     * Adds a where condition to the query.
     *
     * @return self<TModel>
     */
    public function whereField(string $field, mixed $value, string|WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $operator = WhereOperator::fromOperator($operator);

        if ($this->model->hasPrimaryKey() && $field === $this->model->getPrimaryKey() && $this->hasRelationUpdates()) {
            if ($operator === WhereOperator::EQUALS && (is_string($value) || is_int($value) || $value instanceof PrimaryKey)) {
                $this->primaryKeyForRelations = new PrimaryKey($value);
            }
        }

        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        if ($this->wheres->isNotEmpty()) {
            return $this->andWhere($field, $value, $operator);
        }

        $this->wheres[] = new WhereStatement($condition['sql']);
        $this->bind(...$condition['bindings']);

        return $this;
    }

    private function hasOnlyRelationUpdates(): bool
    {
        if (! $this->hasRelationUpdates()) {
            return false;
        }

        foreach (array_keys($this->values) as $field) {
            if (! $this->isRelationField($field)) {
                return false;
            }
        }

        return true;
    }

    private function hasRelationUpdates(): bool
    {
        foreach (array_keys($this->values) as $field) {
            if ($this->isRelationField($field)) {
                return true;
            }
        }

        return false;
    }

    private function isRelationField(string $field): bool
    {
        if (! $this->model) {
            return false;
        }

        return $this->model->getHasMany($field) || $this->model->getHasOne($field);
    }

    private function validateRelationUpdateConstraints(): void
    {
        if (! $this->model->hasPrimaryKey()) {
            throw CouldNotUpdateRelation::requiresPrimaryKey($this->model);
        }

        if ($this->primaryKeyForRelations === null) {
            throw CouldNotUpdateRelation::requiresSingleRecord($this->model);
        }
    }

    private function setWhereForObjectModel(): void
    {
        if (! $this->model->isObjectModel() || ! is_object($this->model->instance) || ! $this->model->hasPrimaryKey()) {
            return;
        }

        if ($primaryKeyValue = $this->model->getPrimaryKeyValue()) {
            $this->whereField($this->model->getPrimaryKey(), $primaryKeyValue->value);
        }
    }
}
