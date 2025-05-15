<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\str;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class BelongsTo implements Relation
{
    public PropertyReflector $property;

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

        $relationModel = model($this->property->getType()->asClass());

        return str($relationModel->getTableName())->singularizeLastWord() . '_' . $relationModel->getPrimaryKey();
    }

    public function getSelectFields(): ImmutableArray
    {
        $relationModel = model($this->property->getType()->asClass());

        return $relationModel
            ->getSelectFields()
            ->map(fn ($field) => new FieldStatement(
                $relationModel->getTableName() . '.' . $field,
            )
                ->withAlias()
                ->withAliasPrefix($this->parent));
    }

    public function getJoinStatement(): JoinStatement
    {
        $relationModel = model($this->property->getType()->asClass());
        $ownerModel = model($this->property->getClass());

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

        return sprintf(
            '%s.%s',
            $relationModel->getTableName(),
            $relationModel->getPrimaryKey(),
        );
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
