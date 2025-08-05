<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeInsterted;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeInserted;
use Tempest\Database\OnDatabase;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\InsertStatement;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\inspect;

/**
 * @template TModel of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModel>
 */
final class InsertQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase;

    private InsertStatement $insert;

    private array $after = [];

    private array $bindings = [];

    private ModelInspector $model;

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
     * Returns the SQL statement without the bindings.
     */
    public function toSql(): ImmutableString
    {
        return $this->build()->toSql();
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
            foreach ($data as $key => $value) {
                if ($this->model->getHasMany($key)) {
                    throw new HasManyRelationCouldNotBeInsterted($this->model->getName(), $key);
                }

                if ($this->model->getHasOne($key)) {
                    throw new HasOneRelationCouldNotBeInserted($this->model->getName(), $key);
                }

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

    private function resolveData(): array
    {
        $entries = [];

        foreach ($this->rows as $model) {
            // Raw entries are straight up added
            if (is_array($model) || $model instanceof ImmutableArray) {
                $entries[] = $model;

                continue;
            }

            // The rest are model objects
            $definition = inspect($model);
            $modelClass = new ClassReflector($model);
            $entry = [];

            // Including all public properties
            foreach ($modelClass->getPublicProperties() as $property) {
                if (! $property->isInitialized($model)) {
                    continue;
                }

                // HasMany and HasOne relations are skipped
                if ($definition->getHasMany($property->getName()) || $definition->getHasOne($property->getName())) {
                    continue;
                }

                $column = $property->getName();
                $value = $property->getValue($model);

                // Skip null primary key values to allow database auto-generation
                if ($property->getType()->getName() === PrimaryKey::class && $value === null) {
                    continue;
                }

                // BelongsTo and reverse HasMany relations are included
                if ($definition->isRelation($property)) {
                    $relationModel = inspect($property->getType()->asClass());
                    $primaryKey = $relationModel->getPrimaryKey() ?? 'id';
                    $column .= '_' . $primaryKey;

                    $value = match (true) {
                        $value === null => null,
                        isset($value->{$primaryKey}) => $value->{$primaryKey}->value,
                        default => new InsertQueryBuilder(
                            $value::class,
                            [$value],
                            $this->serializerFactory,
                        )->build(),
                    };
                }

                // Check if the value needs serialization
                $serializer = $this->serializerFactory->forProperty($property);

                if ($value !== null && $serializer !== null) {
                    $value = $serializer->serialize($value);
                }

                $entry[$column] = $value;
            }

            $entries[] = $entry;
        }

        return $entries;
    }
}
