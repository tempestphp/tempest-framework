<?php

namespace Integration\Database;

use Tempest\Database\BelongsTo;
use Tempest\Database\Config\DatabaseDialect;
use Tests\Tempest\Integration\Database\Fixtures\OwnerModel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\Database\model;

final class BelongsToTest extends FrameworkIntegrationTestCase
{
    public function test_belongs_to(): void
    {
        $model = model(OwnerModel::class);
        $relation = $model->getRelation('relation');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON relation.id = owner.relation_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_relation_join_field(): void
    {
        $model = model(OwnerModel::class);
        $relation = $model->getRelation('relationJoinField');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON relation.overwritten_id = owner.relation_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_relation_join_field_and_table(): void
    {
        $model = model(OwnerModel::class);
        $relation = $model->getRelation('relationJoinFieldAndTable');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON overwritten.overwritten_id = owner.relation_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_owner_join_field(): void
    {
        $model = model(OwnerModel::class);
        $relation = $model->getRelation('ownerJoinField');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON relation.id = owner.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_owner_join_field_and_table(): void
    {
        $model = model(OwnerModel::class);
        $relation = $model->getRelation('ownerJoinFieldAndTable');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON relation.id = overwritten.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }
}