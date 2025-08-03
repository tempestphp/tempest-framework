<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use Tempest\Database\BelongsTo;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasMany;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class BelongsToTest extends FrameworkIntegrationTestCase
{
    public function test_belongs_to(): void
    {
        $model = inspect(BelongsToTestOwnerModel::class);
        $relation = $model->getRelation('relation');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON relation.id = owner.relation_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_relation_join_field(): void
    {
        $model = inspect(BelongsToTestOwnerModel::class);
        $relation = $model->getRelation('relationJoinField');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON relation.overwritten_id = owner.relation_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_relation_join_field_and_table(): void
    {
        $model = inspect(BelongsToTestOwnerModel::class);
        $relation = $model->getRelation('relationJoinFieldAndTable');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON overwritten.overwritten_id = owner.relation_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_owner_join_field(): void
    {
        $model = inspect(BelongsToTestOwnerModel::class);
        $relation = $model->getRelation('ownerJoinField');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON relation.id = owner.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_owner_join_field_and_table(): void
    {
        $model = inspect(BelongsToTestOwnerModel::class);
        $relation = $model->getRelation('ownerJoinFieldAndTable');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON relation.id = overwritten.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_parent(): void
    {
        $model = inspect(BelongsToTestOwnerModel::class);
        $relation = $model->getRelation('relation')->setParent('parent');

        $this->assertSame(
            'relation.name AS `parent.relation.name`',
            $relation->getSelectFields()[0]->compile(DatabaseDialect::SQLITE),
        );
    }
}

#[Table('relation')]
final class BelongsToTestRelationModel
{
    /** @var \Tests\Tempest\Integration\Database\ModelInspector\BelongsToTestOwnerModel[] */
    public array $owners = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\BelongsToTestOwnerModel[] */
    #[HasMany(ownerJoin: 'overwritten_id')]
    public array $ownerJoinField = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\BelongsToTestOwnerModel[] */
    #[HasMany(ownerJoin: 'overwritten.overwritten_id')]
    public array $ownerJoinFieldAndTable = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\BelongsToTestOwnerModel[] */
    #[HasMany(relationJoin: 'overwritten_id')]
    public array $relationJoinField = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\BelongsToTestOwnerModel[] */
    #[HasMany(relationJoin: 'overwritten.overwritten_id')]
    public array $relationJoinFieldAndTable = [];

    public string $name;
}

#[Table('owner')]
final class BelongsToTestOwnerModel
{
    public BelongsToTestRelationModel $relation;

    #[BelongsTo(relationJoin: 'overwritten_id')]
    public BelongsToTestRelationModel $relationJoinField;

    #[BelongsTo(relationJoin: 'overwritten.overwritten_id')]
    public BelongsToTestRelationModel $relationJoinFieldAndTable;

    #[BelongsTo(ownerJoin: 'overwritten_id')]
    public BelongsToTestRelationModel $ownerJoinField;

    #[BelongsTo(ownerJoin: 'overwritten.overwritten_id')]
    public BelongsToTestRelationModel $ownerJoinFieldAndTable;

    public string $name;
}
