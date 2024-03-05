<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use App\Controllers\ValidationController;
use Tempest\Testing\IntegrationTest;
use function Tempest\uri;

/**
 * @internal
 * @small
 */
final class ValidationResponseTest extends IntegrationTest
{
    public function test_validation_errors_are_listed_in_the_response_body()
    {
        $this->markTestSkipped('WIP');

        $this->http->post(uri(ValidationController::class), ['number' => 11, 'item.number' => 11]);
    }
}
