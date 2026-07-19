<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Builder\QueryBuilders\WhereFieldScope;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\WhereExistsStatement;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;
use UnitEnum;

use function Tempest\Support\str;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class HasOne implements Relation
{
    use HasTableAlias;

    public PropertyReflector $property;

    public string $name {
        get => $this->property->getName();
    }

    private ?string $parent = null;

    public function __construct(
        public ?string $ownerJoin = null,
        public ?string $relationJoin = null,
    ) {}

    public function setParent(string $name): self
    {
        $this->parent = $name;

        return $this;
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
        $ownerModel = inspect($this->property->getType()->asClass());
        $relationModel = inspect($this->property->getClass());
        $tableAlias = $this->getTableAlias($ownerModel->getTableName());

        $ownerJoin = $this->getOwnerJoin(
            ownerModel: $ownerModel,
            relationModel: $relationModel,
            tableAlias: $tableAlias,
        );
        $relationJoin = $this->getRelationJoin($relationModel);

        if ($this->isSelfReferencing()) {
            return new JoinStatement(sprintf(
                'LEFT JOIN %s AS %s ON %s = %s',
                $this->quoteIdentifier($ownerModel->getTableName()),
                $this->quoteIdentifier($this->property->getName()),
                $this->quoteIdentifier($ownerJoin),
                $this->quoteIdentifier($relationJoin),
            ));
        }

        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s = %s',
            $this->quoteTableReference($ownerModel->getTableName(), $tableAlias),
            $this->quoteIdentifier($ownerJoin),
            $this->quoteIdentifier($relationJoin),
        ));
    }

    private function getOwnerJoin(ModelInspector $ownerModel, ModelInspector $relationModel, string $tableAlias): string
    {
        $ownerJoin = $this->ownerJoin;
        $tableReference = $this->isSelfReferencing()
            ? $this->property->getName()
            : $tableAlias;

        if ($ownerJoin && ! strpos($ownerJoin, '.')) {
            $ownerJoin = sprintf(
                '%s.%s',
                $tableReference,
                $ownerJoin,
            );
        }

        if ($ownerJoin) {
            return $this->replaceTableReference(
                qualifiedColumn: $ownerJoin,
                originalTable: inspect($this->property->getType()->asClass())->getTableName(),
                aliasedTable: $tableReference,
            );
        }

        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'HasOne');
        }

        return sprintf(
            '%s.%s',
            $tableReference,
            str($relationModel->getTableName())->singularizeLastWord() . '_' . $primaryKey,
        );
    }

    public function getExistsStatement(): WhereExistsStatement
    {
        $relatedModel = inspect(model: $this->property->getType()->asClass());
        $parentModel = inspect(model: $this->property->getClass());

        $relatedTable = $relatedModel->getTableName();
        $parentTable = $parentModel->getTableName();
        $parentPK = $parentModel->getPrimaryKey();
        $fk = (string) ($this->ownerJoin ?? str(string: $parentTable)->singularizeLastWord()->append(suffix: "_{$parentPK}"));

        return new WhereExistsStatement(
            relatedTable: $this->quoteIdentifier($relatedTable),
            relatedModelName: $relatedModel->getName(),
            condition: sprintf(
                '%s = %s',
                $this->qualifyIdentifier($fk, $relatedTable),
                $this->qualifyIdentifier((string) $parentPK, $parentTable),
            ),
        );
    }

    private function isSelfReferencing(): bool
    {
        $relationModel = inspect($this->property->getType()->asClass());
        $ownerModel = inspect($this->property->getClass());

        return $relationModel->getTableName() === $ownerModel->getTableName();
    }

    private function getRelationJoin(ModelInspector $relationModel): string
    {
        $relationJoin = $this->relationJoin;
        $ownerTable = $relationModel->getTableName();

        if ($relationJoin && ! strpos($relationJoin, '.')) {
            $relationJoin = sprintf(
                '%s.%s',
                $ownerTable,
                $relationJoin,
            );
        }

        if ($relationJoin) {
            return $this->replaceTableReference(
                qualifiedColumn: $relationJoin,
                originalTable: $relationModel->getTableName(),
                aliasedTable: $ownerTable,
            );
        }

        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'HasOne');
        }

        return sprintf(
            '%s.%s',
            $ownerTable,
            $primaryKey,
        );
    }

    public function query(PrimaryKey $primaryKey, string|UnitEnum|null $onDatabase = null): QueryBuilder
    {
        $relatedClassName = $this->property->getType()->getName();
        $parentModel = inspect(model: $this->property->getClass());
        $parentTable = $parentModel->getTableName();
        $parentPK = $parentModel->getPrimaryKey();
        $fk = $this->ownerJoin ?? str(string: $parentTable)->singularizeLastWord() . '_' . $parentPK;

        return query(model: $relatedClassName)
            ->onDatabase(databaseTag: $onDatabase)
            ->scope(scope: new WhereFieldScope(field: $fk, value: $primaryKey));
    }
}
