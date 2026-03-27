<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsTo;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class ShouldUsePropertyNameAliasTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function single_relation_is_not_aliased(): void
    {
        $relations = query(model: SingleRelationModel::class)
            ->select()
            ->with('author')
            ->getResolvedRelations();

        $this->assertFalse($relations['author']->withPropertyNameAlias);
    }

    #[Test]
    public function duplicate_target_tables_are_aliased(): void
    {
        $relations = query(model: DuplicateRelationModel::class)
            ->select()
            ->with('createdBy', 'updatedBy')
            ->getResolvedRelations();

        $this->assertTrue($relations['createdBy']->withPropertyNameAlias);
        $this->assertTrue($relations['updatedBy']->withPropertyNameAlias);
    }

    #[Test]
    public function mixed_relations_only_alias_duplicates(): void
    {
        $relations = query(model: MixedRelationModel::class)
            ->select()
            ->with('title', 'createdBy', 'updatedBy')
            ->getResolvedRelations();

        $this->assertFalse($relations['title']->withPropertyNameAlias);
        $this->assertTrue($relations['createdBy']->withPropertyNameAlias);
        $this->assertTrue($relations['updatedBy']->withPropertyNameAlias);
    }

    #[Test]
    public function nested_relations_under_non_duplicate_are_not_aliased(): void
    {
        $relations = query(model: SingleRelationModel::class)
            ->select()
            ->with('author', 'author.posts')
            ->getResolvedRelations();

        $this->assertFalse($relations['author']->withPropertyNameAlias);
        $this->assertFalse($relations['author.posts']->withPropertyNameAlias);
    }
}

#[Table('alias_test_books')]
final class SingleRelationModel
{
    use IsDatabaseModel;

    #[BelongsTo]
    public ?AliasTestAuthor $author = null;

    public string $title;
}

#[Table('alias_test_authors')]
final class AliasTestAuthor
{
    use IsDatabaseModel;

    public string $name;

    /** @var \Tests\Tempest\Integration\Database\Builder\SingleRelationModel[] */
    #[HasMany]
    public array $posts = [];
}

#[Table('alias_test_items')]
final class DuplicateRelationModel
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'created_by')]
    public ?AliasTestUser $createdBy = null;

    #[BelongsTo(ownerJoin: 'updated_by')]
    public ?AliasTestUser $updatedBy = null;

    public string $name;
}

#[Table('alias_test_mixed')]
final class MixedRelationModel
{
    use IsDatabaseModel;

    #[HasOne]
    public ?AliasTestTitle $title = null;

    #[BelongsTo(ownerJoin: 'created_by')]
    public ?AliasTestUser $createdBy = null;

    #[BelongsTo(ownerJoin: 'updated_by')]
    public ?AliasTestUser $updatedBy = null;

    public string $name;
}

#[Table('alias_test_users')]
final class AliasTestUser
{
    use IsDatabaseModel;

    public string $name;
}

#[Table('alias_test_titles')]
final class AliasTestTitle
{
    use IsDatabaseModel;

    public string $value;
}
