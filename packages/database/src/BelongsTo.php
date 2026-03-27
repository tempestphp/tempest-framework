<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\WhereExistsStatement;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;

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

        return str($relationModel->getTableName())->singularizeLastWord() . '_' . $primaryKey;
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
                $relationModel->getTableName(),
                $this->property->getName(),
                $relationJoin,
                $ownerJoin,
            ));
        }

        $tableName = $relationModel->getTableName();
        $tableRef = $tableAlias !== $tableName
            ? sprintf('%s AS %s', $tableName, $tableAlias)
            : $tableName;

        // LEFT JOIN authors ON authors.id = books.author_id
        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s = %s',
            $tableRef,
            $relationJoin,
            $ownerJoin,
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
            return $relationJoin;
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
            relatedTable: $relatedTable,
            relatedModelName: $relatedModel->getName(),
            condition: "{$relatedTable}.{$relatedPK} = {$parentTable}.{$fk}",
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

        if ($ownerJoin && ! strpos($ownerJoin, '.')) {
            $ownerJoin = sprintf('%s.%s', $ownerModel->getTableName(), $ownerJoin);
        }

        if ($ownerJoin) {
            return $ownerJoin;
        }

        return sprintf(
            '%s.%s',
            $ownerModel->getTableName(),
            $this->getOwnerFieldName(),
        );
    }
}
