<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasOneThrough;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class HasOneThroughTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function has_one_through(): void
    {
        $model = inspect(model: HasOneThroughOwnerModel::class);
        $relation = $model->getRelation(name: 'target');

        $this->assertInstanceOf(expected: HasOneThrough::class, actual: $relation);
        $this->assertSame(
            expected: 'LEFT JOIN intermediate ON intermediate.owner_id = owner.id LEFT JOIN target ON target.intermediate_id = intermediate.id',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_one_through_select_fields(): void
    {
        $model = inspect(model: HasOneThroughOwnerModel::class);
        $relation = $model->getRelation(name: 'target');

        $fields = $relation->getSelectFields();

        $this->assertSame(
            expected: 'target.id AS `target.id`',
            actual: $fields[0]->compile(DatabaseDialect::SQLITE),
        );
        $this->assertSame(
            expected: 'target.intermediate_id AS `target.intermediate_id`',
            actual: $fields[1]->compile(DatabaseDialect::SQLITE),
        );
        $this->assertSame(
            expected: 'target.data AS `target.data`',
            actual: $fields[2]->compile(DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_one_through_with_parent(): void
    {
        $model = inspect(model: HasOneThroughOwnerModel::class);
        $relation = $model
            ->getRelation(name: 'target')
            ->setParent(name: 'parent');

        $this->assertSame(
            expected: 'parent_target.data AS `parent.target.data`',
            actual: $relation->getSelectFields()[2]->compile(DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_one_through_with_custom_joins(): void
    {
        $model = inspect(model: HasOneThroughCustomOwnerModel::class);
        $relation = $model->getRelation(name: 'target');

        $this->assertInstanceOf(expected: HasOneThrough::class, actual: $relation);
        $this->assertSame(
            expected: 'LEFT JOIN custom_intermediate ON custom_intermediate.custom_owner_fk = custom_owner.custom_pk LEFT JOIN target ON target.custom_intermediate_fk = custom_intermediate.custom_intermediate_pk',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }
}

#[Table(name: 'owner')]
final class HasOneThroughOwnerModel
{
    public PrimaryKey $id;

    #[HasOneThrough(through: HasOneThroughIntermediateModel::class)]
    public ?HasOneThroughTargetModel $target = null;

    public string $name;
}

#[Table(name: 'intermediate')]
final class HasOneThroughIntermediateModel
{
    public PrimaryKey $id;

    public HasOneThroughOwnerModel $owner;

    public string $value;
}

#[Table(name: 'target')]
final class HasOneThroughTargetModel
{
    public PrimaryKey $id;

    public HasOneThroughIntermediateModel $intermediate;

    public string $data;
}

#[Table(name: 'custom_owner')]
final class HasOneThroughCustomOwnerModel
{
    public PrimaryKey $custom_pk;

    #[HasOneThrough(
        through: HasOneThroughCustomIntermediateModel::class,
        ownerJoin: 'custom_owner_fk',
        relationJoin: 'custom_pk',
        throughOwnerJoin: 'custom_intermediate_fk',
        throughRelationJoin: 'custom_intermediate_pk',
    )]
    public ?HasOneThroughTargetModel $target = null;

    public string $name;
}

#[Table(name: 'custom_intermediate')]
final class HasOneThroughCustomIntermediateModel
{
    public PrimaryKey $custom_intermediate_pk;

    public string $value;
}
