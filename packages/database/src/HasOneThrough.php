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

use function Tempest\Support\str;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final class HasOneThrough implements Relation
{
    use HasTableAlias;

    public PropertyReflector $property;

    public string $name {
        get => $this->property->getName();
    }

    private ?string $parent = null;

    /**
     * @param class-string $through
     */
    public function __construct(
        public string $through,
        public ?string $ownerJoin = null,
        public ?string $relationJoin = null,
        public ?string $throughOwnerJoin = null,
        public ?string $throughRelationJoin = null,
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
                ->getType()
                ->asClass(),
        );

        return $targetModel
            ->getSelectFields()
            ->map(
                map: fn (
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
                    ->withAliasPrefix(prefix: $this->parent),
            );
    }

    public function getJoinStatement(): JoinStatement
    {
        $intermediateModel = inspect(model: $this->through);

        return new JoinStatement(
            statement: sprintf(
                '%s %s',
                $this->buildFirstJoin(
                    ownerModel: inspect(model: $this->property->getClass()),
                    intermediateModel: $intermediateModel,
                ),
                $this->buildSecondJoin(
                    intermediateModel: $intermediateModel,
                    targetModel: inspect(
                        model: $this->property
                            ->getType()
                            ->asClass(),
                    ),
                ),
            ),
        );
    }

    private function buildFirstJoin(
        ModelInspector $ownerModel,
        ModelInspector $intermediateModel,
    ): string {
        return sprintf(
            'LEFT JOIN %s ON %s = %s',
            $this->quoteIdentifier($intermediateModel->getTableName()),
            $this->quoteIdentifier($this->resolveOwnerJoin(intermediateModel: $intermediateModel, ownerModel: $ownerModel)),
            $this->quoteIdentifier($this->resolveRelationJoin(ownerModel: $ownerModel)),
        );
    }

    private function buildSecondJoin(
        ModelInspector $intermediateModel,
        ModelInspector $targetModel,
    ): string {
        $tableAlias = $this->getTableAlias(tableName: $targetModel->getTableName());

        return sprintf(
            'LEFT JOIN %s ON %s = %s',
            $this->quoteTableReference($targetModel->getTableName(), $tableAlias),
            $this->quoteIdentifier($this->resolveThroughOwnerJoin(
                targetModel: $targetModel,
                intermediateModel: $intermediateModel,
                tableAlias: $tableAlias,
            )),
            $this->quoteIdentifier($this->resolveThroughRelationJoin(intermediateModel: $intermediateModel)),
        );
    }

    private function resolveOwnerJoin(
        ModelInspector $intermediateModel,
        ModelInspector $ownerModel,
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
                $intermediateModel->getTableName(),
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
                relationType: 'HasOneThrough',
            );
        }

        return sprintf(
            '%s.%s',
            $intermediateModel->getTableName(),
            str(string: $ownerModel->getTableName())->singularizeLastWord() . '_' . $primaryKey,
        );
    }

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
                relationType: 'HasOneThrough',
            );
        }

        return sprintf(
            '%s.%s',
            $ownerTable,
            $primaryKey,
        );
    }

    private function resolveThroughOwnerJoin(
        ModelInspector $targetModel,
        ModelInspector $intermediateModel,
        string $tableAlias,
    ): string {
        $throughOwnerJoin = $this->throughOwnerJoin;

        if (
            $throughOwnerJoin
            && ! strpos(
                haystack: $throughOwnerJoin,
                needle: '.',
            )
        ) {
            return sprintf(
                '%s.%s',
                $tableAlias,
                $throughOwnerJoin,
            );
        }

        if ($throughOwnerJoin) {
            return $throughOwnerJoin;
        }

        $primaryKey = $intermediateModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation(
                model: $intermediateModel->getName(),
                relationType: 'HasOneThrough',
            );
        }

        return sprintf(
            '%s.%s',
            $tableAlias,
            str(string: $intermediateModel->getTableName())->singularizeLastWord() . '_' . $primaryKey,
        );
    }

    private function resolveThroughRelationJoin(ModelInspector $intermediateModel): string
    {
        $throughRelationJoin = $this->throughRelationJoin;

        if (
            $throughRelationJoin
            && ! strpos(
                haystack: $throughRelationJoin,
                needle: '.',
            )
        ) {
            return sprintf(
                '%s.%s',
                $intermediateModel->getTableName(),
                $throughRelationJoin,
            );
        }

        if ($throughRelationJoin) {
            return $throughRelationJoin;
        }

        $primaryKey = $intermediateModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation(
                model: $intermediateModel->getName(),
                relationType: 'HasOneThrough',
            );
        }

        return sprintf(
            '%s.%s',
            $intermediateModel->getTableName(),
            $primaryKey,
        );
    }

    public function getExistsStatement(): WhereExistsStatement
    {
        $ownerModel = inspect(model: $this->property->getClass());
        $intermediateModel = inspect(model: $this->through);
        $targetModel = inspect(model: $this->property->getType()->asClass());

        $intermediateTable = $intermediateModel->getTableName();
        $ownerTable = $ownerModel->getTableName();
        $ownerPK = $ownerModel->getPrimaryKey();

        $fk = $this->ownerJoin ?? str(string: $ownerTable)->singularizeLastWord()->append(suffix: "_{$ownerPK}");

        $targetTable = $targetModel->getTableName();
        $intermediatePK = $intermediateModel->getPrimaryKey();
        $targetFK = $this->throughOwnerJoin ?? str(string: $intermediateTable)->singularizeLastWord()->append(suffix: "_{$intermediatePK}");

        return new WhereExistsStatement(
            relatedTable: $this->quoteIdentifier($intermediateTable),
            relatedModelName: $targetModel->getName(),
            condition: sprintf(
                '%s = %s',
                $this->qualifyIdentifier((string) $fk, $intermediateTable),
                $this->qualifyIdentifier((string) $ownerPK, $ownerTable),
            ),
            joinStatement: new JoinStatement(
                statement: sprintf(
                    'INNER JOIN %s ON %s = %s',
                    $this->quoteIdentifier($targetTable),
                    $this->qualifyIdentifier((string) $targetFK, $targetTable),
                    $this->qualifyIdentifier((string) $intermediatePK, $intermediateTable),
                ),
            ),
        );
    }

    public function query(PrimaryKey $primaryKey, string|UnitEnum|null $onDatabase = null): QueryBuilder
    {
        $relatedClassName = $this->property->getType()->getName();
        $ownerModel = inspect(model: $this->property->getClass());
        $intermediateModel = inspect(model: $this->through);
        $intermediateTable = $intermediateModel->getTableName();
        $ownerTable = $ownerModel->getTableName();
        $ownerPK = $ownerModel->getPrimaryKey();
        $intermediatePK = $intermediateModel->getPrimaryKey();
        $relatedTable = inspect(model: $relatedClassName)->getTableName();

        $ownerFK = $this->ownerJoin ?? str(string: $ownerTable)->singularizeLastWord() . '_' . $ownerPK;
        $targetFK = $this->throughOwnerJoin ?? str(string: $intermediateTable)->singularizeLastWord() . '_' . $intermediatePK;

        return query(model: $relatedClassName)
            ->onDatabase(databaseTag: $onDatabase)
            ->scope(scope: new WhereRawScope(
                statement: sprintf(
                    '%s IN (SELECT %s FROM %s WHERE %s = ?)',
                    $this->qualifyIdentifier((string) $targetFK, $relatedTable),
                    $this->quoteIdentifier((string) $intermediatePK),
                    $this->quoteIdentifier($intermediateTable),
                    $this->quoteIdentifier((string) $ownerFK),
                ),
                binding: $primaryKey,
            ));
    }
}
