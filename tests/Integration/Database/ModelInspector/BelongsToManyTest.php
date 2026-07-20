<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsToMany;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class BelongsToManyTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function belongs_to_many(): void
    {
        $model = inspect(model: BelongsToManyOwnerModel::class);
        $relation = $model->getRelation(name: 'targets');

        $this->assertInstanceOf(
            expected: BelongsToMany::class,
            actual: $relation,
        );
        $this->assertSame(
            expected: 'LEFT JOIN owner_target ON owner_target.owner_id = owner.id LEFT JOIN target ON target.id = owner_target.target_id',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function belongs_to_many_select_fields(): void
    {
        $model = inspect(model: BelongsToManyOwnerModel::class);
        $relation = $model->getRelation(name: 'targets');

        $fields = $relation->getSelectFields();

        $this->assertSame(
            expected: 'target.id AS `targets.id`',
            actual: $fields[0]->compile(DatabaseDialect::SQLITE),
        );
        $this->assertSame(
            expected: 'target.data AS `targets.data`',
            actual: $fields[1]->compile(DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function belongs_to_many_with_parent(): void
    {
        $model = inspect(model: BelongsToManyOwnerModel::class);
        $relation = $model
            ->getRelation(name: 'targets')
            ->setParent(name: 'parent');

        $this->assertSame(
            expected: 'parent_targets.data AS `parent.targets.data`',
            actual: $relation->getSelectFields()[1]->compile(DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function belongs_to_many_with_parent_join_uses_alias(): void
    {
        $model = inspect(model: BelongsToManyOwnerModel::class);
        $relation = $model
            ->getRelation(name: 'targets')
            ->setParent(name: 'parent');

        $this->assertSame(
            expected: 'LEFT JOIN owner_target ON owner_target.owner_id = owner.id LEFT JOIN target AS parent_targets ON parent_targets.id = owner_target.target_id',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function belongs_to_many_with_custom_pivot(): void
    {
        $model = inspect(model: BelongsToManyCustomOwnerModel::class);
        $relation = $model->getRelation(name: 'targets');

        $this->assertInstanceOf(
            expected: BelongsToMany::class,
            actual: $relation,
        );
        $this->assertSame(
            expected: 'LEFT JOIN custom_pivot ON custom_pivot.custom_owner_fk = custom_owner.custom_pk LEFT JOIN target ON target.id = custom_pivot.custom_target_fk',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }
}

#[Table(name: 'owner')]
final class BelongsToManyOwnerModel
{
    public PrimaryKey $id;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\BelongsToManyTargetModel[] */
    #[BelongsToMany]
    public array $targets = [];

    public string $name;
}

#[Table(name: 'target')]
final class BelongsToManyTargetModel
{
    public PrimaryKey $id;

    public string $data;
}

#[Table(name: 'custom_owner')]
final class BelongsToManyCustomOwnerModel
{
    public PrimaryKey $custom_pk;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\BelongsToManyTargetModel[] */
    #[BelongsToMany(pivot: 'custom_pivot', ownerJoin: 'custom_owner_fk', relationJoin: 'custom_pk', relatedOwnerJoin: 'custom_target_fk')]
    public array $targets = [];

    public string $name;
}
