<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use App\Controllers\ValidationController;
use Tempest\Http\Session\Session;
use function Tempest\uri;
use Tests\Tempest\Integration\FrameworkIntegrationTest;

/**
 * @internal
 * @small
 */
final class ValidationResponseTest extends FrameworkIntegrationTest
{
    public function test_validation_errors_are_listed_in_the_response_body()
    {
        $this->http
            ->post(uri([ValidationController::class, 'store']), ['number' => 11, 'item.number' => 11])
            ->assertRedirect(uri([ValidationController::class, 'store']))
            ->assertHasValidationError('number');

        $this->http
            ->get(uri([ValidationController::class, 'get']))
            ->assertOk()
            ->assertHasNoValidationsErrors();
    }

    public function test_original_values(): void
    {
        $values = ['number' => 11, 'item.number' => 11];

        $this->http
            ->post(uri([ValidationController::class, 'store']), $values)
            ->assertRedirect(uri([ValidationController::class, 'store']))
            ->assertHasValidationError('number')
            ->assertHasSession(Session::ORIGINAL_VALUES, function (Session $session, array $data) use ($values) {
                $this->assertEquals($values, $data);
            });
    }
}
