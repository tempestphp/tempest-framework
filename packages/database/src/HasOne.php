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
final class HasOne implements Relation
{
    public PropertyReflector $property;

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
        $relationModel = model($this->property->getType()->asClass());

        return $relationModel
            ->getSelectFields()
            ->map(fn ($field) => new FieldStatement(
                $relationModel->getTableName() . '.' . $field,
            )->withAlias()->withAliasPrefix($this->parent));
    }

    public function getJoinStatement(): JoinStatement
    {
        $ownerModel = model($this->property->getType()->asClass());
        $relationModel = model($this->property->getClass());

        $ownerJoin = $this->getOwnerJoin($ownerModel, $relationModel);
        $relationJoin = $this->getRelationJoin($relationModel);

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

        if ($ownerJoin && ! strpos($ownerJoin, '.')) {
            $ownerJoin = sprintf(
                '%s.%s',
                $ownerModel->getTableName(),
                $ownerJoin,
            );
        }

        if ($ownerJoin) {
            return $ownerJoin;
        }

        return sprintf(
            '%s.%s',
            $ownerModel->getTableName(),
            str($relationModel->getTableName())->singularizeLastWord() . '_' . $relationModel->getPrimaryKey(),
        );
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

        return sprintf(
            '%s.%s',
            $relationModel->getTableName(),
            $relationModel->getPrimaryKey(),
        );
    }
}
