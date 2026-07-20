<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsTo;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class SnakeCaseRelationTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function belongs_to_with_snake_case_property(): void
    {
        $model = inspect(model: SnakeCaseBookModel::class);
        $relation = $model->getRelation(name: 'main_author');

        $this->assertInstanceOf(
            expected: BelongsTo::class,
            actual: $relation,
        );
        $this->assertSame(
            expected: 'LEFT JOIN author ON author.id = book.main_author_id',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_one_with_snake_case_property(): void
    {
        $model = inspect(model: SnakeCaseAuthorModel::class);
        $relation = $model->getRelation(name: 'author_profile');

        $this->assertInstanceOf(
            expected: HasOne::class,
            actual: $relation,
        );
        $this->assertSame(
            expected: 'LEFT JOIN profile ON profile.author_id = author.id',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_many_with_snake_case_property(): void
    {
        $model = inspect(model: SnakeCaseAuthorModel::class);
        $relation = $model->getRelation(name: 'written_books');

        $this->assertInstanceOf(
            expected: HasMany::class,
            actual: $relation,
        );
        $this->assertSame(
            expected: 'LEFT JOIN book ON book.author_id = author.id',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function snake_case_property_is_recognized_as_relation(): void
    {
        $model = inspect(model: SnakeCaseBookModel::class);

        $this->assertTrue(condition: $model->isRelation(name: 'main_author'));
    }

    #[Test]
    public function snake_case_property_excluded_from_select_fields(): void
    {
        $model = inspect(model: SnakeCaseAuthorModel::class);
        $fields = $model->getSelectFields();

        $fieldNames = [];
        foreach ($fields as $field) {
            $fieldNames[] = $field;
        }

        $this->assertNotContains(
            needle: 'author_profile',
            haystack: $fieldNames,
        );
        $this->assertNotContains(
            needle: 'written_books',
            haystack: $fieldNames,
        );
        $this->assertContains(
            needle: 'name',
            haystack: $fieldNames,
        );
    }
}

#[Table(name: 'book')]
final class SnakeCaseBookModel
{
    public PrimaryKey $id;

    public SnakeCaseAuthorModel $main_author;

    public string $title;
}

#[Table(name: 'author')]
final class SnakeCaseAuthorModel
{
    public PrimaryKey $id;

    #[HasOne]
    public ?SnakeCaseProfileModel $author_profile = null;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\SnakeCaseBookModel[] */
    public array $written_books = [];

    public string $name;
}

#[Table(name: 'profile')]
final class SnakeCaseProfileModel
{
    public PrimaryKey $id;

    public SnakeCaseAuthorModel $author;

    public string $bio;
}
