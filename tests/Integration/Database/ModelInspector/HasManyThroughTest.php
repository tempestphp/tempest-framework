<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasManyThrough;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class HasManyThroughTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function has_many_through(): void
    {
        $model = inspect(model: HasManyThroughOwnerModel::class);
        $relation = $model->getRelation(name: 'targets');

        $this->assertInstanceOf(
            expected: HasManyThrough::class,
            actual: $relation,
        );

        $this->assertSame(
            expected: 'LEFT JOIN intermediate ON intermediate.owner_id = owner.id LEFT JOIN target ON target.intermediate_id = intermediate.id',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_many_through_select_fields(): void
    {
        $model = inspect(model: HasManyThroughOwnerModel::class);
        $relation = $model->getRelation(name: 'targets');

        $fields = $relation->getSelectFields();

        $this->assertSame(
            expected: 'target.id AS `targets.id`',
            actual: $fields[0]->compile(DatabaseDialect::SQLITE),
        );
        $this->assertSame(
            expected: 'target.intermediate_id AS `targets.intermediate_id`',
            actual: $fields[1]->compile(DatabaseDialect::SQLITE),
        );
        $this->assertSame(
            expected: 'target.data AS `targets.data`',
            actual: $fields[2]->compile(DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_many_through_with_parent(): void
    {
        $model = inspect(model: HasManyThroughOwnerModel::class);
        $relation = $model
            ->getRelation(name: 'targets')
            ->setParent(name: 'parent');

        $this->assertSame(
            expected: 'parent_targets.data AS `parent.targets.data`',
            actual: $relation->getSelectFields()[2]->compile(DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_many_through_with_custom_joins(): void
    {
        $model = inspect(model: HasManyThroughCustomOwnerModel::class);
        $relation = $model->getRelation(name: 'targets');

        $this->assertInstanceOf(
            expected: HasManyThrough::class,
            actual: $relation,
        );

        $this->assertSame(
            expected: 'LEFT JOIN custom_intermediate ON custom_intermediate.custom_owner_fk = custom_owner.custom_pk LEFT JOIN target ON target.custom_intermediate_fk = custom_intermediate.custom_intermediate_pk',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }
}

#[Table(name: 'owner')]
final class HasManyThroughOwnerModel
{
    public PrimaryKey $id;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\HasManyThroughTargetModel[] */
    #[HasManyThrough(through: HasManyThroughIntermediateModel::class)]
    public array $targets = [];

    public string $name;
}

#[Table(name: 'intermediate')]
final class HasManyThroughIntermediateModel
{
    public PrimaryKey $id;

    public HasManyThroughOwnerModel $owner;

    public string $value;
}

#[Table(name: 'target')]
final class HasManyThroughTargetModel
{
    public PrimaryKey $id;

    public HasManyThroughIntermediateModel $intermediate;

    public string $data;
}

#[Table(name: 'custom_owner')]
final class HasManyThroughCustomOwnerModel
{
    public PrimaryKey $custom_pk;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\HasManyThroughTargetModel[] */
    #[HasManyThrough(
        through: HasManyThroughCustomIntermediateModel::class,
        ownerJoin: 'custom_owner_fk',
        relationJoin: 'custom_pk',
        throughOwnerJoin: 'custom_intermediate_fk',
        throughRelationJoin: 'custom_intermediate_pk',
    )]
    public array $targets = [];

    public string $name;
}

#[Table(name: 'custom_intermediate')]
final class HasManyThroughCustomIntermediateModel
{
    public PrimaryKey $custom_intermediate_pk;

    public string $value;
}
