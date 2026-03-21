<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\str;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class HasMany implements Relation
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
        $relationModel = inspect($this->property->getIterableType()->asClass());
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

    public function primaryKey(): string
    {
        $relationModel = inspect($this->property->getIterableType()->asClass());
        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'HasMany');
        }

        return $primaryKey;
    }

    public function idField(): string
    {
        $relationModel = inspect($this->property->getIterableType()->asClass());
        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'HasMany');
        }

        return sprintf(
            '%s.%s',
            $this->property->getName(),
            $primaryKey,
        );
    }

    public function getJoinStatement(): JoinStatement
    {
        $ownerModel = inspect($this->property->getIterableType()->asClass());
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
                $ownerModel->getTableName(),
                $this->property->getName(),
                $ownerJoin,
                $relationJoin,
            ));
        }

        $tableName = $ownerModel->getTableName();
        $tableRef = $tableAlias !== $tableName
            ? sprintf('%s AS %s', $tableName, $tableAlias)
            : $tableName;

        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s = %s',
            $tableRef,
            $ownerJoin,
            $relationJoin,
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
            return $ownerJoin;
        }

        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'HasMany');
        }

        return sprintf(
            '%s.%s',
            $tableReference,
            str($relationModel->getTableName())->singularizeLastWord() . '_' . $primaryKey,
        );
    }

    private function isSelfReferencing(): bool
    {
        $relationModel = inspect($this->property->getIterableType()->asClass());
        $ownerModel = inspect($this->property->getClass());

        return $relationModel->getTableName() === $ownerModel->getTableName();
    }

    private function getRelationJoin(ModelInspector $relationModel): string
    {
        $relationJoin = $this->relationJoin;

        if ($relationJoin && ! strpos($relationJoin, '.')) {
            $relationJoin = sprintf(
                '%s.%s',
                $relationModel->getTableName(),
                $relationJoin,
            );
        }

        if ($relationJoin) {
            return $relationJoin;
        }

        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'HasMany');
        }

        return sprintf(
            '%s.%s',
            $relationModel->getTableName(),
            $primaryKey,
        );
    }
}
