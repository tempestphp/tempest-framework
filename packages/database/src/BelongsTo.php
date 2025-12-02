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
final class BelongsTo implements Relation
{
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
            } else {
                return $this->ownerJoin;
            }
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
            : $relationModel->getTableName();

        return $relationModel
            ->getSelectFields()
            ->map(function ($field) use ($tableReference) {
                return new FieldStatement(
                    $tableReference . '.' . $field,
                )
                    ->withAlias(
                        sprintf('%s.%s', $this->property->getName(), $field),
                    )
                    ->withAliasPrefix($this->parent);
            });
    }

    public function getJoinStatement(): JoinStatement
    {
        $relationModel = inspect($this->property->getType()->asClass());
        $ownerModel = inspect($this->property->getClass());

        $relationJoin = $this->getRelationJoin($relationModel);
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

        // LEFT JOIN authors ON authors.id = books.author_id
        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s = %s',
            $relationModel->getTableName(),
            $relationJoin,
            $ownerJoin,
        ));
    }

    private function getRelationJoin(ModelInspector $relationModel): string
    {
        $relationJoin = $this->relationJoin;
        $tableReference = $this->isSelfReferencing()
            ? $this->property->getName()
            : $relationModel->getTableName();

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
