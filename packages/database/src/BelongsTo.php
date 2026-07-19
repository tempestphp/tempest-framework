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

#[Attribute(Attribute::TARGET_PROPERTY)]
final class BelongsTo implements Relation
{
    use HasTableAlias;

    public PropertyReflector $property;

    public string $name {
        get => $this->property->getName();
    }

    private ?string $parent = null;

    public function __construct(
        private readonly ?string $relationJoin = null,
        private readonly ?string $ownerJoin = null,
    ) {}

    public function setParent(string $name): self
    {
        $this->parent = $name;

        return $this;
    }

    public function getOwnerFieldName(): string
    {
        if ($this->ownerJoin) {
            if (str_contains($this->ownerJoin, '.')) {
                return explode('.', $this->ownerJoin)[1];
            }

            return $this->ownerJoin;
        }

        $relationModel = inspect($this->property->getType()->asClass());
        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'BelongsTo');
        }

        return str($this->property->getName())->snake() . '_' . $primaryKey;
    }

    public function getSelectFields(): ImmutableArray
    {
        $relationModel = inspect($this->property->getType()->asClass());
        $tableReference = $this->isSelfReferencing()
            ? $this->property->getName()
            : $this->getTableAlias($relationModel->getTableName());

        return $relationModel
            ->getSelectFields()
            ->map(fn ($field) => new FieldStatement(
                $tableReference . '.' . $field,
            )
                ->withAlias(
                    sprintf('%s.%s', $this->property->getName(), $field),
                )
                ->withAliasPrefix($this->parent));
    }

    public function getJoinStatement(): JoinStatement
    {
        $relationModel = inspect($this->property->getType()->asClass());
        $ownerModel = inspect($this->property->getClass());
        $tableAlias = $this->getTableAlias($relationModel->getTableName());

        $relationJoin = $this->getRelationJoin(
            relationModel: $relationModel,
            tableAlias: $tableAlias,
        );
        $ownerJoin = $this->getOwnerJoin($ownerModel);

        if ($this->isSelfReferencing()) {
            return new JoinStatement(sprintf(
                'LEFT JOIN %s AS %s ON %s = %s',
                $this->quoteIdentifier($relationModel->getTableName()),
                $this->quoteIdentifier($this->property->getName()),
                $this->quoteIdentifier($relationJoin),
                $this->quoteIdentifier($ownerJoin),
            ));
        }

        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s = %s',
            $this->quoteTableReference($relationModel->getTableName(), $tableAlias),
            $this->quoteIdentifier($relationJoin),
            $this->quoteIdentifier($ownerJoin),
        ));
    }

    private function getRelationJoin(ModelInspector $relationModel, string $tableAlias): string
    {
        $relationJoin = $this->relationJoin;
        $tableReference = $this->isSelfReferencing()
            ? $this->property->getName()
            : $tableAlias;

        if ($relationJoin && ! strpos($relationJoin, '.')) {
            $relationJoin = sprintf('%s.%s', $tableReference, $relationJoin);
        }

        if ($relationJoin) {
            return $this->replaceTableReference(
                qualifiedColumn: $relationJoin,
                originalTable: $relationModel->getTableName(),
                aliasedTable: $tableAlias,
            );
        }

        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'BelongsTo');
        }

        return sprintf('%s.%s', $tableReference, $primaryKey);
    }

    public function getExistsStatement(): WhereExistsStatement
    {
        $relatedModel = inspect(model: $this->property->getType()->asClass());
        $parentModel = inspect(model: $this->property->getClass());

        $relatedTable = $relatedModel->getTableName();
        $parentTable = $parentModel->getTableName();
        $relatedPK = $relatedModel->getPrimaryKey();
        $fk = $this->getOwnerFieldName();

        return new WhereExistsStatement(
            relatedTable: $this->quoteIdentifier($relatedTable),
            relatedModelName: $relatedModel->getName(),
            condition: sprintf(
                '%s = %s',
                $this->qualifyIdentifier((string) $relatedPK, $relatedTable),
                $this->qualifyIdentifier($fk, $parentTable),
            ),
        );
    }

    private function isSelfReferencing(): bool
    {
        $relationModel = inspect($this->property->getType()->asClass());
        $ownerModel = inspect($this->property->getClass());

        return $relationModel->getTableName() === $ownerModel->getTableName();
    }

    private function getOwnerJoin(ModelInspector $ownerModel): string
    {
        $ownerJoin = $this->ownerJoin;
        $ownerTable = $ownerModel->getTableName();

        if ($ownerJoin && ! strpos($ownerJoin, '.')) {
            $ownerJoin = sprintf('%s.%s', $ownerTable, $ownerJoin);
        }

        if ($ownerJoin) {
            return $this->replaceTableReference(
                qualifiedColumn: $ownerJoin,
                originalTable: $ownerModel->getTableName(),
                aliasedTable: $ownerTable,
            );
        }

        return sprintf(
            '%s.%s',
            $ownerTable,
            $this->getOwnerFieldName(),
        );
    }

    public function query(PrimaryKey $primaryKey, string|UnitEnum|null $onDatabase = null): QueryBuilder
    {
        $relatedClassName = $this->property->getType()->getName();
        $relatedModel = inspect(model: $this->property->getType()->asClass());
        $ownerModel = inspect(model: $this->property->getClass());
        $relatedTable = $relatedModel->getTableName();
        $relatedPK = $relatedModel->getPrimaryKey();
        $ownerTable = $ownerModel->getTableName();
        $ownerPK = $ownerModel->getPrimaryKey();
        $fk = $this->getOwnerFieldName();

        return query(model: $relatedClassName)
            ->onDatabase(databaseTag: $onDatabase)
            ->scope(scope: new WhereRawScope(
                statement: sprintf(
                    '%s = (SELECT %s FROM %s WHERE %s = ?)',
                    $this->qualifyIdentifier((string) $relatedPK, $relatedTable),
                    $this->quoteIdentifier($fk),
                    $this->quoteIdentifier($ownerTable),
                    $this->qualifyIdentifier((string) $ownerPK, $ownerTable),
                ),
                binding: $primaryKey,
            ));
    }
}
