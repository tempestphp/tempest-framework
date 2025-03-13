<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Router\Session\Session;
use Tests\Tempest\Fixtures\Controllers\ValidationController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\uri;

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
            ->assertHasSession(Session::ORIGINAL_VALUES, function (Session $session, array $data) use ($values): void {
                $this->assertEquals($values, $data);
            });
    }
}
