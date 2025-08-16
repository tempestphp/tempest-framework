<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use Tempest\Database\BelongsTo;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class HasOneTest extends FrameworkIntegrationTestCase
{
    public function test_has_one(): void
    {
        $model = inspect(HasOneTestRelationModel::class);
        $relation = $model->getRelation('owner');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_overwritten_owner_join_field(): void
    {
        $model = inspect(HasOneTestRelationModel::class);
        $relation = $model->getRelation('ownerJoinField');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.overwritten_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_overwritten_owner_join_field_and_table(): void
    {
        $model = inspect(HasOneTestRelationModel::class);
        $relation = $model->getRelation('ownerJoinFieldAndTable');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON overwritten.overwritten_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_overwritten_relation_join_field(): void
    {
        $model = inspect(HasOneTestRelationModel::class);
        $relation = $model->getRelation('relationJoinField');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = relation.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_overwritten_relation_join_field_and_table(): void
    {
        $model = inspect(HasOneTestRelationModel::class);
        $relation = $model->getRelation('relationJoinFieldAndTable');

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = overwritten.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_with_parent(): void
    {
        $model = inspect(HasOneTestRelationModel::class);
        $relation = $model->getRelation('owner')->setParent('parent');

        $this->assertSame(
            'owner.relation_id AS `parent.owner.relation_id`',
            $relation->getSelectFields()[1]->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_one_throws_exception_for_model_without_primary_key(): void
    {
        $model = inspect(HasOneTestRelationNoIdModel::class);
        $relation = $model->getRelation('owner');

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage(
            "`Tests\Tempest\Integration\Database\ModelInspector\HasOneTestRelationNoIdModel` does not have a primary column defined, which is required for `HasOne` relationships.",
        );

        $relation->getJoinStatement();
    }
}

#[Table('relation')]
final class HasOneTestRelationModel
{
    #[HasOne]
    public HasOneTestOwnerModel $owner;

    #[HasOne(ownerJoin: 'overwritten_id')]
    public HasOneTestOwnerModel $ownerJoinField;

    #[HasOne(ownerJoin: 'overwritten.overwritten_id')]
    public HasOneTestOwnerModel $ownerJoinFieldAndTable;

    #[HasOne(relationJoin: 'overwritten_id')]
    public HasOneTestOwnerModel $relationJoinField;

    #[HasOne(relationJoin: 'overwritten.overwritten_id')]
    public HasOneTestOwnerModel $relationJoinFieldAndTable;

    public string $name;
}

#[Table('owner')]
final class HasOneTestOwnerModel
{
    public PrimaryKey $id;

    public HasOneTestRelationModel $relation;

    #[BelongsTo(relationJoin: 'overwritten_id')]
    public HasOneTestRelationModel $relationJoinField;

    #[BelongsTo(relationJoin: 'overwritten.overwritten_id')]
    public HasOneTestRelationModel $relationJoinFieldAndTable;

    #[BelongsTo(ownerJoin: 'overwritten_id')]
    public HasOneTestRelationModel $ownerJoinField;

    #[BelongsTo(ownerJoin: 'overwritten.overwritten_id')]
    public HasOneTestRelationModel $ownerJoinFieldAndTable;

    public string $name;
}

#[Table('relation')]
final class HasOneTestRelationNoIdModel
{
    #[HasOne]
    public HasOneTestOwnerNoIdModel $owner;

    public string $name;
}

#[Table('owner')]
final class HasOneTestOwnerNoIdModel
{
    public HasOneTestRelationNoIdModel $relation;

    public string $name;
}
