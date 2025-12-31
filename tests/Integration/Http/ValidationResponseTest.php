<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\Session\Session;
use Tests\Tempest\Fixtures\Controllers\ValidationController;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Router\uri;

/**
 * @internal
 */
final class ValidationResponseTest extends FrameworkIntegrationTestCase
{
    public function test_validation_errors_are_listed_in_the_response_body(): void
    {
        $this->http
            ->post(
                uri: uri([ValidationController::class, 'store']),
                body: ['number' => 11, 'item.number' => 11],
                headers: ['referer' => uri([ValidationController::class, 'store'])],
            )
            ->assertRedirect(uri([ValidationController::class, 'store']))
            ->assertHasValidationError('number');
    }

    public function test_original_values(): void
    {
        $values = ['number' => 11, 'item.number' => 11];

        $this->http
            ->post(
                uri: uri([ValidationController::class, 'store']),
                body: $values,
                headers: ['referer' => uri([ValidationController::class, 'store'])],
            )
            ->assertRedirect(uri([ValidationController::class, 'store']))
            ->assertHasValidationError('number')
            ->assertHasSession(Session::ORIGINAL_VALUES, function (Session $_session, array $data) use ($values): void {
                $this->assertEquals($values, $data);
            });
    }

    public function test_update_book(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
        );

        $book = Book::create(
            title: 'Timeline Taxi',
            author: Author::create(name: 'Brent'),
        );

        $this->http
            ->post(
                uri([ValidationController::class, 'updateBook'], book: 1),
                body: ['title' => 'Beyond the Odyssee'],
            )
            ->assertOk()
            ->assertHasNoJsonValidationErrors();

        $book->refresh();

        $this->assertSame($book->title, 'Beyond the Odyssee');
    }

    public function test_failing_post_request(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::create(
            title: 'Timeline Taxi',
            author: Author::create(name: 'Brent'),
        );

        $this->http
            ->post(
                uri([ValidationController::class, 'updateBook'], book: 1),
                body: ['book' => ['title' => 1]],
            )
            ->assertHasJsonValidationErrors(['title' => ['Value must be between 1 and 120']]);

        $this->assertSame('Timeline Taxi', Book::find(id: 1)->first()->title);
    }

    public function test_sensitive_fields_are_excluded_from_original_values(): void
    {
        $this->http
            ->post(
                uri: uri([ValidationController::class, 'storeSensitive']),
                body: ['not_sensitive_param' => '', 'sensitive_param' => 'secret123'],
                headers: ['referer' => '/test-sensitive-validation'],
            )
            ->assertHasValidationError('not_sensitive_param')
            ->assertHasSession(Session::ORIGINAL_VALUES, function (Session $_session, array $data): void {
                $this->assertArrayNotHasKey('sensitive_param', $data);
                $this->assertArrayHasKey('not_sensitive_param', $data);
            });
    }
}
