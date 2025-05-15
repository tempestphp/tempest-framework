<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Support\str;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class BelongsTo implements Relation
{
    public PropertyReflector $property;

    public function __construct(
        public ?string $relationJoin = null,
        public ?string $ownerJoin = null,
    ) {}

    public function getOwnerFieldName(): string
    {
        if ($this->ownerJoin) {
            return explode('.', $this->ownerJoin)[1];
        }

        $relationModel = model($this->property->getType()->asClass());

        return str($relationModel->getTableName())->singularizeLastWord() . '_' . $relationModel->getPrimaryKey();
    }

    public function getSelectFields(): ImmutableArray
    {
        $relationModel = model($this->property->getType()->asClass());

        return $relationModel
            ->getSelectFields()
            ->map(fn ($field) => new FieldStatement($relationModel->getTableName() . '.' . $field)->withAlias());
    }

    public function getJoinStatement(): JoinStatement
    {
        $relationModel = model($this->property->getType()->asClass());

        // authors.id
        $relationJoin = $this->relationJoin;

        if (! $relationJoin) {
            $relationJoin = sprintf(
                '%s.%s',
                $relationModel->getTableName(),
                $relationModel->getPrimaryKey(),
            );
        }

        // books.author_id
        $ownerJoin = $this->ownerJoin;

        if (! $ownerJoin) {
            $ownerModel = model($this->property->getClass());

            $ownerJoin = sprintf(
                '%s.%s',
                $ownerModel->getTableName(),
                $this->getOwnerFieldName(),
            );
        }

        // LEFT JOIN authors ON authors.id = books.author_id
        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s = %s',
            $relationModel->getTableName(),
            $relationJoin,
            $ownerJoin,
        ));
    }
}
