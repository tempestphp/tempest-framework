<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Builder\QueryBuilders\WhereRawScope;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\WhereExistsStatement;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;
use UnitEnum;

use function Tempest\Support\arr;
use function Tempest\Support\str;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final class BelongsToMany implements Relation
{
    use HasTableAlias;

    public PropertyReflector $property;

    public string $name {
        get => $this->property->getName();
    }

    private ?string $parent = null;

    public function __construct(
        public ?string $pivot = null,
        public ?string $ownerJoin = null,
        public ?string $relationJoin = null,
        public ?string $relatedOwnerJoin = null,
        public ?string $relatedRelationJoin = null,
    ) {}

    public function setParent(string $name): self
    {
        $this->parent = $name;

        return $this;
    }

    public function getSelectFields(): ImmutableArray
    {
        $targetModel = inspect(
            model: $this->property
                ->getIterableType()
                ->asClass(),
        );

        return $targetModel
            ->getSelectFields()
            ->map(map: fn (
                $field,
            ) => new FieldStatement(
                field: "{$this->getTableAlias(tableName: $targetModel->getTableName())}.{$field}",
            )
                ->withAlias(
                    alias: sprintf(
                        '%s.%s',
                        $this->property->getName(),
                        $field,
                    ),
                )
                ->withAliasPrefix(prefix: $this->parent));
    }

    public function primaryKey(): string
    {
        $relationModel = inspect(
            model: $this->property
                ->getIterableType()
                ->asClass(),
        );
        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation(
                model: $relationModel->getName(),
                relationType: 'BelongsToMany',
            );
        }

        return $primaryKey;
    }

    public function idField(): string
    {
        $relationModel = inspect(
            model: $this->property
                ->getIterableType()
                ->asClass(),
        );
        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation(
                model: $relationModel->getName(),
                relationType: 'BelongsToMany',
            );
        }

        return sprintf(
            '%s.%s',
            $this->property->getName(),
            $primaryKey,
        );
    }

    public function getJoinStatement(): JoinStatement
    {
        $ownerModel = inspect(model: $this->property->getClass());
        $targetModel = inspect(
            model: $this->property
                ->getIterableType()
                ->asClass(),
        );
        $pivotTable = $this->resolvePivotTable(
            ownerModel: $ownerModel,
            targetModel: $targetModel,
        );
        return new JoinStatement(
            statement: sprintf(
                '%s %s',
                $this->buildFirstJoin(
                    ownerModel: $ownerModel,
                    pivotTable: $pivotTable,
                ),
                $this->buildSecondJoin(
                    targetModel: $targetModel,
                    pivotTable: $pivotTable,
                    tableAlias: $this->getTableAlias(tableName: $targetModel->getTableName()),
                ),
            ),
        );
    }

    private function resolvePivotTable(
        ModelInspector $ownerModel,
        ModelInspector $targetModel,
    ): string {
        if ($this->pivot) {
            return $this->pivot;
        }

        return arr([$ownerModel->getTableName(), $targetModel->getTableName()])
            ->sort()
            ->implode('_')
            ->toString();
    }

    /**
     * LEFT JOIN pivot ON pivot.owner_id = owner.id
     */
    private function buildFirstJoin(
        ModelInspector $ownerModel,
        string $pivotTable,
    ): string {
        return sprintf(
            'LEFT JOIN %s ON %s = %s',
            $this->quoteIdentifier($pivotTable),
            $this->quoteIdentifier($this->resolveOwnerJoin(
                ownerModel: $ownerModel,
                pivotTable: $pivotTable,
            )),
            $this->quoteIdentifier($this->resolveRelationJoin(ownerModel: $ownerModel)),
        );
    }

    /**
     * LEFT JOIN target ON target.id = pivot.target_id
     */
    private function buildSecondJoin(
        ModelInspector $targetModel,
        string $pivotTable,
        string $tableAlias,
    ): string {
        return sprintf(
            'LEFT JOIN %s ON %s = %s',
            $this->quoteTableReference($targetModel->getTableName(), $tableAlias),
            $this->quoteIdentifier($this->resolveRelatedRelationJoin(
                targetModel: $targetModel,
                tableAlias: $tableAlias,
            )),
            $this->quoteIdentifier($this->resolveRelatedOwnerJoin(
                targetModel: $targetModel,
                pivotTable: $pivotTable,
            )),
        );
    }

    /**
     * FK on pivot pointing to owner: pivot.{owner_singular}_id
     */
    private function resolveOwnerJoin(
        ModelInspector $ownerModel,
        string $pivotTable,
    ): string {
        $ownerJoin = $this->ownerJoin;

        if (
            $ownerJoin
            && ! strpos(
                haystack: $ownerJoin,
                needle: '.',
            )
        ) {
            return sprintf(
                '%s.%s',
                $pivotTable,
                $ownerJoin,
            );
        }

        if ($ownerJoin) {
            return $ownerJoin;
        }

        $primaryKey = $ownerModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation(
                model: $ownerModel->getName(),
                relationType: 'BelongsToMany',
            );
        }

        return sprintf(
            '%s.%s',
            $pivotTable,
            str(string: $ownerModel->getTableName())->singularizeLastWord() . '_' . $primaryKey,
        );
    }

    /**
     * PK on owner: owner.id
     */
    private function resolveRelationJoin(ModelInspector $ownerModel): string
    {
        $relationJoin = $this->relationJoin;
        $ownerTable = $ownerModel->getTableName();

        if (
            $relationJoin
            && ! strpos(
                haystack: $relationJoin,
                needle: '.',
            )
        ) {
            return sprintf(
                '%s.%s',
                $ownerTable,
                $relationJoin,
            );
        }

        if ($relationJoin) {
            return $this->replaceTableReference(
                qualifiedColumn: $relationJoin,
                originalTable: $ownerModel->getTableName(),
                aliasedTable: $ownerTable,
            );
        }

        $primaryKey = $ownerModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation(
                model: $ownerModel->getName(),
                relationType: 'BelongsToMany',
            );
        }

        return sprintf(
            '%s.%s',
            $ownerTable,
            $primaryKey,
        );
    }

    /**
     * FK on pivot pointing to target: pivot.{target_singular}_id
     */
    private function resolveRelatedOwnerJoin(
        ModelInspector $targetModel,
        string $pivotTable,
    ): string {
        $relatedOwnerJoin = $this->relatedOwnerJoin;

        if (
            $relatedOwnerJoin
            && ! strpos(
                haystack: $relatedOwnerJoin,
                needle: '.',
            )
        ) {
            return sprintf(
                '%s.%s',
                $pivotTable,
                $relatedOwnerJoin,
            );
        }

        if ($relatedOwnerJoin) {
            return $relatedOwnerJoin;
        }

        $primaryKey = $targetModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation(
                model: $targetModel->getName(),
                relationType: 'BelongsToMany',
            );
        }

        return sprintf(
            '%s.%s',
            $pivotTable,
            str(string: $targetModel->getTableName())->singularizeLastWord() . '_' . $primaryKey,
        );
    }

    /**
     * PK on target: target.id
     */
    private function resolveRelatedRelationJoin(ModelInspector $targetModel, string $tableAlias): string
    {
        $relatedRelationJoin = $this->relatedRelationJoin;

        if (
            $relatedRelationJoin
            && ! strpos(
                haystack: $relatedRelationJoin,
                needle: '.',
            )
        ) {
            return sprintf(
                '%s.%s',
                $tableAlias,
                $relatedRelationJoin,
            );
        }

        if ($relatedRelationJoin) {
            return $relatedRelationJoin;
        }

        $primaryKey = $targetModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation(
                model: $targetModel->getName(),
                relationType: 'BelongsToMany',
            );
        }

        return sprintf(
            '%s.%s',
            $tableAlias,
            $primaryKey,
        );
    }

    public function getExistsStatement(): WhereExistsStatement
    {
        $ownerModel = inspect(model: $this->property->getClass());
        $targetModel = inspect(model: $this->property->getIterableType()->asClass());
        $pivotTable = $this->resolvePivotTable(ownerModel: $ownerModel, targetModel: $targetModel);

        $ownerPK = $ownerModel->getPrimaryKey();
        $ownerTable = $ownerModel->getTableName();
        $fk = $this->ownerJoin ?? str(string: $ownerTable)->singularizeLastWord()->append(suffix: "_{$ownerPK}");

        $targetTable = $targetModel->getTableName();
        $targetPK = $targetModel->getPrimaryKey();
        $targetFK = $this->relatedOwnerJoin ?? str(string: $targetTable)->singularizeLastWord()->append(suffix: "_{$targetPK}");

        return new WhereExistsStatement(
            relatedTable: $this->quoteIdentifier($pivotTable),
            relatedModelName: $targetModel->getName(),
            condition: sprintf(
                '%s = %s',
                $this->qualifyIdentifier((string) $fk, $pivotTable),
                $this->qualifyIdentifier((string) $ownerPK, $ownerTable),
            ),
            joinStatement: new JoinStatement(
                statement: sprintf(
                    'INNER JOIN %s ON %s = %s',
                    $this->quoteIdentifier($targetTable),
                    $this->qualifyIdentifier((string) $targetPK, $targetTable),
                    $this->qualifyIdentifier((string) $targetFK, $pivotTable),
                ),
            ),
        );
    }

    public function query(PrimaryKey $primaryKey, string|UnitEnum|null $onDatabase = null): QueryBuilder
    {
        $ownerModel = inspect(model: $this->property->getClass());
        $targetModel = inspect(model: $this->property->getIterableType()->asClass());
        $relatedClassName = $this->property->getIterableType()->getName();
        $ownerTable = $ownerModel->getTableName();
        $ownerPK = $ownerModel->getPrimaryKey();
        $targetTable = $targetModel->getTableName();
        $targetPK = $targetModel->getPrimaryKey();

        $pivotTable = $this->resolvePivotTable(ownerModel: $ownerModel, targetModel: $targetModel);
        $ownerFK = $this->ownerJoin ?? str(string: $ownerTable)->singularizeLastWord() . '_' . $ownerPK;
        $targetFK = $this->relatedOwnerJoin ?? str(string: $targetTable)->singularizeLastWord() . '_' . $targetPK;

        return query(model: $relatedClassName)
            ->onDatabase(databaseTag: $onDatabase)
            ->scope(scope: new WhereRawScope(
                statement: sprintf(
                    '%s IN (SELECT %s FROM %s WHERE %s = ?)',
                    $this->qualifyIdentifier((string) $targetPK, $targetTable),
                    $this->quoteIdentifier((string) $targetFK),
                    $this->quoteIdentifier($pivotTable),
                    $this->quoteIdentifier((string) $ownerFK),
                ),
                binding: $primaryKey,
            ));
    }
}
