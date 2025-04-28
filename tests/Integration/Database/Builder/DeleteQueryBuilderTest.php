<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\DeleteQueryBuilder;
use Tempest\Database\Id;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class DeleteQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_delete_on_plain_table(): void
    {
        $query = query('foo')
            ->delete()
            ->where('`bar` = ?', 'boo')
            ->build();

        $this->assertSame(
            <<<SQL
            DELETE FROM `foo`
            WHERE `bar` = ?
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            'boo',
            $query->bindings[0],
        );
    }

    public function test_delete_on_model_table(): void
    {
        $query = query(Author::class)
            ->delete()
            ->allowAll()
            ->build();

        $this->assertSame(
            <<<SQL
            DELETE FROM `authors`
            SQL,
            $query->getSql(),
        );
    }

    public function test_delete_on_model_object(): void
    {
        $author = new Author(name: 'brent');
        $author->id = new Id(10);

        $query = query($author)
            ->delete()
            ->build();

        $this->assertSame(
            <<<SQL
            DELETE FROM `authors`
            WHERE `id` = :id
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            10,
            $query->bindings['id']->id,
        );
    }

    public function test_delete_on_plain_table_with_conditions(): void
    {
        $query = query('foo')
            ->delete()
            ->when(
                true,
                fn (DeleteQueryBuilder $query) => $query->where('`bar` = ?', 'boo'),
            )
            ->when(
                false,
                fn (DeleteQueryBuilder $query) => $query->where('`bar` = ?', 'foo'),
            )
            ->build();

        $this->assertSame(
            <<<SQL
            DELETE FROM `foo`
            WHERE `bar` = ?
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            'boo',
            $query->bindings[0],
        );
    }
}
