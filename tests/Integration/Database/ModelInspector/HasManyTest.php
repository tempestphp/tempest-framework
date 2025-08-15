<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use Tempest\Database\BelongsTo;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\HasMany;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class HasManyTest extends FrameworkIntegrationTestCase
{
    public function test_has_many(): void
    {
        $model = inspect(HasManyTestRelationModel::class);
        $relation = $model->getRelation('owners');

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_many_with_overwritten_owner_join_field(): void
    {
        $model = inspect(HasManyTestRelationModel::class);
        $relation = $model->getRelation('ownerJoinField');

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.overwritten_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_many_with_overwritten_owner_join_field_and_table(): void
    {
        $model = inspect(HasManyTestRelationModel::class);
        $relation = $model->getRelation('ownerJoinFieldAndTable');

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON overwritten.overwritten_id = relation.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_many_with_overwritten_relation_join_field(): void
    {
        $model = inspect(HasManyTestRelationModel::class);
        $relation = $model->getRelation('relationJoinField');

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = relation.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_many_with_overwritten_relation_join_field_and_table(): void
    {
        $model = inspect(HasManyTestRelationModel::class);
        $relation = $model->getRelation('relationJoinFieldAndTable');

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(
            'LEFT JOIN owner ON owner.relation_id = overwritten.overwritten_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_many_with_parent(): void
    {
        $model = inspect(HasManyTestRelationModel::class);
        $relation = $model->getRelation('owners')->setParent('parent');

        $this->assertSame(
            'owner.relation_id AS `parent.owners.relation_id`',
            $relation->getSelectFields()[1]->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_has_many_throws_exception_for_model_without_primary_key(): void
    {
        $model = inspect(HasManyTestRelationWithoutIdModel::class);
        $relation = $model->getRelation('owners');

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage(
            "`Tests\Tempest\Integration\Database\ModelInspector\HasManyTestRelationWithoutIdModel` does not have a primary column defined, which is required for `HasMany` relationships.",
        );

        $relation->getJoinStatement();
    }
}

#[Table('relation')]
final class HasManyTestRelationModel
{
    public PrimaryKey $id;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\HasManyTestOwnerModel[] */
    public array $owners = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\HasManyTestOwnerModel[] */
    #[HasMany(ownerJoin: 'overwritten_id')]
    public array $ownerJoinField = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\HasManyTestOwnerModel[] */
    #[HasMany(ownerJoin: 'overwritten.overwritten_id')]
    public array $ownerJoinFieldAndTable = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\HasManyTestOwnerModel[] */
    #[HasMany(relationJoin: 'overwritten_id')]
    public array $relationJoinField = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\HasManyTestOwnerModel[] */
    #[HasMany(relationJoin: 'overwritten.overwritten_id')]
    public array $relationJoinFieldAndTable = [];

    public string $name;
}

#[Table('relation')]
final class HasManyTestRelationWithoutIdModel
{
    /** @var \Tests\Tempest\Integration\Database\ModelInspector\HasManyTestOwnerWithoutIdModel[] */
    public array $owners = [];

    public string $name;
}

#[Table('owner')]
final class HasManyTestOwnerModel
{
    public PrimaryKey $id;

    public HasManyTestRelationModel $relation;

    #[BelongsTo(relationJoin: 'overwritten_id')]
    public HasManyTestRelationModel $relationJoinField;

    #[BelongsTo(relationJoin: 'overwritten.overwritten_id')]
    public HasManyTestRelationModel $relationJoinFieldAndTable;

    #[BelongsTo(ownerJoin: 'overwritten_id')]
    public HasManyTestRelationModel $ownerJoinField;

    #[BelongsTo(ownerJoin: 'overwritten.overwritten_id')]
    public HasManyTestRelationModel $ownerJoinFieldAndTable;

    public string $name;
}

#[Table('owner')]
final class HasManyTestOwnerWithoutIdModel
{
    public HasManyTestRelationWithoutIdModel $relation;

    public string $name;
}
