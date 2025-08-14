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

        return $relationModel
            ->getSelectFields()
            ->map(function ($field) use ($relationModel) {
                return new FieldStatement(
                    $relationModel->getTableName() . '.' . $field,
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

        if ($relationJoin && ! strpos($relationJoin, '.')) {
            $relationJoin = sprintf('%s.%s', $relationModel->getTableName(), $relationJoin);
        }

        if ($relationJoin) {
            return $relationJoin;
        }

        $primaryKey = $relationModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relationModel->getName(), 'BelongsTo');
        }

        return sprintf('%s.%s', $relationModel->getTableName(), $primaryKey);
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
