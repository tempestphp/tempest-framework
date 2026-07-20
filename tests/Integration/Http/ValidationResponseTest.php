<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\ContentType;
use Tempest\Http\Session\FormSession;
use Tests\Tempest\Fixtures\Controllers\ValidationController;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
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
    #[Test]
    public function validation_errors_are_listed_in_the_response_body(): void
    {
        $this->http
            ->as(ContentType::HTML)
            ->post(
                uri: uri([ValidationController::class, 'store']),
                body: ['number' => 11, 'item.number' => 11],
                headers: ['referer' => uri([ValidationController::class, 'store'])],
            )
            ->assertRedirect(uri([ValidationController::class, 'store']))
            ->assertHasValidationError('number');
    }

    #[Test]
    public function original_values(): void
    {
        $values = ['number' => 11, 'item.number' => 11];

        $this->http
            ->as(contentType::HTML)
            ->post(
                uri: uri([ValidationController::class, 'store']),
                body: $values,
                headers: ['referer' => uri([ValidationController::class, 'store'])],
            )
            ->assertRedirect(uri([ValidationController::class, 'store']))
            ->assertHasValidationError('number')
            ->assertHasFormOriginalValues($values);
    }

    #[Test]
    public function update_book(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
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

    #[Test]
    public function failing_post_request(): void
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
            ->assertHasJsonValidationErrors(['title' => ['title must be between 1 and 120', 'title must be a string']]);

        $this->assertSame('Timeline Taxi', Book::find(id: 1)->first()->title);
    }

    #[Test]
    public function sensitive_fields_are_excluded_from_original_values(): void
    {
        $this->http
            ->as(ContentType::HTML)
            ->post(
                uri: uri([ValidationController::class, 'storeSensitive']),
                body: ['not_sensitive_param' => '', 'sensitive_param' => 'secret123'],
                headers: ['referer' => '/test-sensitive-validation'],
            )
            ->assertHasValidationError('not_sensitive_param')
            ->assertHasForm(function (FormSession $form): void {
                $this->assertNull($form->getOriginalValueFor('sensitive_param'));
                $this->assertNotNull($form->getOriginalValueFor('not_sensitive_param'));
            });
    }
}
