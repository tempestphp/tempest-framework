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
final class HasMany implements Relation
{
    public PropertyReflector $property;

    public string $fieldName {
        get => $this->property->getName() . '.' . $this->localPropertyName;
    }

    public function __construct(
        public ?string $relationJoin = null,
        public ?string $ownerJoin = null,
    ) {}

    public function getSelectFields(): ImmutableArray
    {
        $relationModel = model($this->property->getIterableType()->asClass());

        return $relationModel
            ->getSelectFields()
            ->map(fn ($field) => new FieldStatement($relationModel->getTableName() . '.' . $field)->withAlias());
    }

    public function idField(): string
    {
        $relationModel = model($this->property->getIterableType()->asClass());

        return sprintf(
            '%s.%s',
            $relationModel->getTableName(),
            $relationModel->getPrimaryKey(),
        );
    }

    public function getJoinStatement(): JoinStatement
    {
        $relationModel = model($this->property->getIterableType()->asClass());
        $ownerModel = model($this->property->getClass());

        // chapters.book_id
        $relationJoin = $this->relationJoin;

        if (! $relationJoin) {
            $relationJoin = sprintf(
                '%s.%s',
                $relationModel->getTableName(),
                str($ownerModel->getTableName())->singularizeLastWord() . '_' . $ownerModel->getPrimaryKey(),
            );
        }

        // books.id
        $ownerJoin = $this->ownerJoin;

        if (! $ownerJoin) {
            $ownerJoin = sprintf(
                '%s.%s',
                $ownerModel->getTableName(),
                $ownerModel->getPrimaryKey(),
            );
        }

        // LEFT JOIN chapters ON chapters.book_id = books.id
        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s = %s',
            $relationModel->getTableName(),
            $relationJoin,
            $ownerJoin,
        ));
    }
}
