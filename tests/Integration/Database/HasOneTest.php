<?php

namespace Integration\Database;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasOne;
use Tests\Tempest\Integration\Database\Fixtures\HasOneRelationModel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\model;

final class HasOneTest extends FrameworkIntegrationTestCase
{
    public function test_has_one(): void
    {
        $model = model(HasOneRelationModel::class);
        $relation = $model->getRelation('owner');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_overwritten_owner_join_field(): void
    {
        $model = model(HasOneRelationModel::class);
        $relation = $model->getRelation('ownerJoinField');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.overwritten_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_overwritten_owner_join_field_and_table(): void
    {
        $model = model(HasOneRelationModel::class);
        $relation = $model->getRelation('ownerJoinFieldAndTable');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON overwritten.overwritten_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_overwritten_relation_join_field(): void
    {
        $model = model(HasOneRelationModel::class);
        $relation = $model->getRelation('relationJoinField');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = relation.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_overwritten_relation_join_field_and_table(): void
    {
        $model = model(HasOneRelationModel::class);
        $relation = $model->getRelation('relationJoinFieldAndTable');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = overwritten.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }
}
