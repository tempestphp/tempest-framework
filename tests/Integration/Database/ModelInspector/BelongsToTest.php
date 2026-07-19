<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsTo;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Config\PostgresConfig;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\GenericDatabase;
use Tempest\Database\HasMany;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBus;
use Tempest\Mapper\SerializerFactory;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class BelongsToTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function relation_queries_quote_camel_case_identifiers_for_postgresql(): void
    {
        $connection = new PDOConnection(new PostgresConfig());
        $this->container->singleton(
            Database::class,
            new GenericDatabase(
                connection: $connection,
                transactionManager: new GenericTransactionManager($connection),
                serializerFactory: $this->container->get(SerializerFactory::class),
                eventBus: $this->container->get(EventBus::class),
            ),
        );

        $items = inspect(PostgresRelationStash::class)->getRelation('items');
        $stash = inspect(PostgresRelationStashItem::class)->getRelation('stash');

        $this->assertSame(
            'LEFT JOIN "stash_items" ON "stash_items"."stashId" = "stashes"."id"',
            $items->getJoinStatement()->compile(DatabaseDialect::POSTGRESQL),
        );
        $this->assertSame(
            'EXISTS (SELECT 1 FROM "stash_items" WHERE "stash_items"."stashId" = "stashes"."id")',
            $items->getExistsStatement()->compile(DatabaseDialect::POSTGRESQL),
        );
        $this->assertSame(
            'LEFT JOIN "stashes" ON "stashes"."id" = "stash_items"."stashId"',
            $stash->getJoinStatement()->compile(DatabaseDialect::POSTGRESQL),
        );
        $this->assertSame(
            'EXISTS (SELECT 1 FROM "stashes" WHERE "stashes"."id" = "stash_items"."stashId")',
            $stash->getExistsStatement()->compile(DatabaseDialect::POSTGRESQL),
        );
        $this->assertSame(
            'SELECT "stashes"."id" AS "stashes.id" FROM "stashes" WHERE "stashes"."id" = (SELECT "stashId" FROM "stash_items" WHERE "stash_items"."id" = ?)',
            $stash->query(new PrimaryKey(1))->select()->compile()->toString(),
        );
    }

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
            'LEFT JOIN relation ON relation.overwritten_id = owner.relation_join_field_id',
            $relation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_belongs_to_with_relation_join_field_and_table(): void
    {
        $model = inspect(BelongsToTestOwnerModel::class);
        $relation = $model->getRelation('relationJoinFieldAndTable');

        $this->assertInstanceOf(BelongsTo::class, $relation);

        $this->assertEquals(
            'LEFT JOIN relation ON overwritten.overwritten_id = owner.relation_join_field_and_table_id',
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
            'parent_relation.name AS `parent.relation.name`',
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

    public function test_multiple_belongs_to_same_table_generates_distinct_joins(): void
    {
        $model = inspect(BelongsToTestRoleWithMultipleSameTableRelationsModel::class);

        $createdByRelation = $model->getRelation('createdBy')->setParent('')->withPropertyNameAlias();
        $updatedByRelation = $model->getRelation('updatedBy')->setParent('')->withPropertyNameAlias();

        $this->assertEquals(
            'LEFT JOIN users AS createdBy ON createdBy.id = roles.created_by',
            $createdByRelation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );

        $this->assertEquals(
            'LEFT JOIN users AS updatedBy ON updatedBy.id = roles.updated_by',
            $updatedByRelation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_multiple_belongs_to_same_table_with_full_table_column_syntax(): void
    {
        $model = inspect(BelongsToTestRoleWithFullSpecRelationsModel::class);

        $createdByRelation = $model->getRelation('created_by')->setParent('')->withPropertyNameAlias();
        $updatedByRelation = $model->getRelation('updated_by')->setParent('')->withPropertyNameAlias();

        $this->assertEquals(
            'LEFT JOIN users AS created_by ON created_by.id = roles.created_by',
            $createdByRelation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );

        $this->assertEquals(
            'LEFT JOIN users AS updated_by ON updated_by.id = roles.updated_by',
            $updatedByRelation->getJoinStatement()->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_multiple_belongs_to_same_table_select_fields(): void
    {
        $model = inspect(BelongsToTestRoleWithMultipleSameTableRelationsModel::class);

        $createdByFields = $model->getRelation('createdBy')->setParent('')->withPropertyNameAlias()->getSelectFields();
        $updatedByFields = $model->getRelation('updatedBy')->setParent('')->withPropertyNameAlias()->getSelectFields();

        $this->assertSame(
            'createdBy.id AS `createdBy.id`',
            $createdByFields[0]->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            'updatedBy.id AS `updatedBy.id`',
            $updatedByFields[0]->compile(DatabaseDialect::SQLITE),
        );
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

#[Table('stashes')]
final class PostgresRelationStash
{
    public PrimaryKey $id;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\PostgresRelationStashItem[] */
    #[HasMany(ownerJoin: 'stashId')]
    public array $items = [];
}

#[Table('stash_items')]
final class PostgresRelationStashItem
{
    public PrimaryKey $id;

    #[BelongsTo(ownerJoin: 'stashId')]
    public PostgresRelationStash $stash;

    public string $stashId;
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

#[Table('users')]
final class BelongsToTestUserModel
{
    public PrimaryKey $id;

    public string $name;
}

#[Table('roles')]
final class BelongsToTestRoleWithMultipleSameTableRelationsModel
{
    public PrimaryKey $id;

    #[BelongsTo(ownerJoin: 'created_by')]
    public BelongsToTestUserModel $createdBy;

    #[BelongsTo(ownerJoin: 'updated_by')]
    public BelongsToTestUserModel $updatedBy;

    public string $name;
}

#[Table('roles')]
final class BelongsToTestRoleWithFullSpecRelationsModel
{
    public PrimaryKey $id;

    #[BelongsTo(relationJoin: 'users.id', ownerJoin: 'roles.created_by')]
    public ?BelongsToTestUserModel $created_by = null;

    #[BelongsTo(relationJoin: 'users.id', ownerJoin: 'roles.updated_by')]
    public ?BelongsToTestUserModel $updated_by = null;

    public string $name;
}

#[Table('categories')]
final class SelfReferencingCategoryModel
{
    public PrimaryKey $id;

    public ?int $parent_id = null;

    public string $name;

    #[BelongsTo(relationJoin: 'id', ownerJoin: 'parent_id')]
    public ?SelfReferencingCategoryModel $parent = null;

    #[BelongsTo(relationJoin: 'id', ownerJoin: 'category_parent_id')]
    public ?SelfReferencingCategoryModel $parentWithCustomOwnerJoin = null;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\SelfReferencingCategoryModel[] */
    #[HasMany(ownerJoin: 'parent_id', relationJoin: 'id')]
    public array $children = [];
}
