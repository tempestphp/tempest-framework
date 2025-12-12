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
            : $relationModel->getTableName();

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

        $ownerJoin = $this->getOwnerJoin($ownerModel, $relationModel);
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

        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s = %s',
            $ownerModel->getTableName(),
            $ownerJoin,
            $relationJoin,
        ));
    }

    private function getOwnerJoin(ModelInspector $ownerModel, ModelInspector $relationModel): string
    {
        $ownerJoin = $this->ownerJoin;
        $tableReference = $this->isSelfReferencing()
            ? $this->property->getName()
            : $ownerModel->getTableName();

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
