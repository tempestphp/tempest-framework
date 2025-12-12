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
            $relation->getSelectFields()[1]->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_throws_exception_for_model_without_primary_key(): void
    {
        $model = inspect(BelongsToTestOwnerWithoutIdModel::class);
        $relation = $model->getRelation('relation');

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage(
            "`Tests\Tempest\Integration\Database\ModelInspector\BelongsToTestRelationWithoutIdModel` does not have a primary column defined, which is required for `BelongsTo` relationships.",
        );

        $relation->getJoinStatement();
    }

    public function test_self_referencing_belongs_to(): void
    {
        $model = inspect(SelfReferencingCategoryModel::class);
        $relation = $model->getRelation('parent');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN categories AS parent ON parent.id = categories.parent_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_self_referencing_belongs_to_select_fields(): void
    {
        $model = inspect(SelfReferencingCategoryModel::class);
        $relation = $model->getRelation('parent');

        $selectFields = $relation->getSelectFields();

        $this->assertSame(
            'parent.id AS `parent.id`',
            $selectFields[0]->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            'parent.parent_id AS `parent.parent_id`',
            $selectFields[1]->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            'parent.name AS `parent.name`',
            $selectFields[2]->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_self_referencing_belongs_to_with_custom_owner_join(): void
    {
        $model = inspect(SelfReferencingCategoryModel::class);
        $relation = $model->getRelation('parentWithCustomOwnerJoin');

        $this->assertEquals(
            'LEFT JOIN categories AS parentWithCustomOwnerJoin ON parentWithCustomOwnerJoin.id = categories.category_parent_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_self_referencing_has_many(): void
    {
        $model = inspect(SelfReferencingCategoryModel::class);
        $relation = $model->getRelation('children');

        $this->assertInstanceOf(HasMany::class, $relation);

        $this->assertEquals(
            'LEFT JOIN categories AS children ON children.parent_id = categories.id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_self_referencing_has_many_select_fields(): void
    {
        $model = inspect(SelfReferencingCategoryModel::class);
        $relation = $model->getRelation('children');

        $selectFields = $relation->getSelectFields();

        $this->assertSame(
            'children.id AS `children.id`',
            $selectFields[0]->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            'children.parent_id AS `children.parent_id`',
            $selectFields[1]->compile(DatabaseDialect::SQLITE),
        );
    }
}

#[Table('relation')]
final class BelongsToTestRelationModel
{
    public PrimaryKey $id;

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
    public PrimaryKey $id;

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

    public BelongsToTestRelationModel $relationNoPrimaryKey;
}

#[Table('relation_no_primary_key')]
final class BelongsToTestRelationWithoutIdModel
{
    public string $name;
}

#[Table('owner_no_primary_key')]
final class BelongsToTestOwnerWithoutIdModel
{
    public BelongsToTestRelationWithoutIdModel $relation;

    public string $name;
}

#[Table('categories')]
final class SelfReferencingCategoryModel
{
    public PrimaryKey $id;

    public ?int $parent_id = null;

    public string $name;

    #[BelongsTo(ownerJoin: 'parent_id', relationJoin: 'id')]
    public ?SelfReferencingCategoryModel $parent = null;

    #[BelongsTo(ownerJoin: 'category_parent_id', relationJoin: 'id')]
    public ?SelfReferencingCategoryModel $parentWithCustomOwnerJoin = null;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\SelfReferencingCategoryModel[] */
    #[HasMany(ownerJoin: 'parent_id', relationJoin: 'id')]
    public array $children = [];
}
