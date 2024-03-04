<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use App\Controllers\ValidationController;
use Tempest\Testing\IntegrationTest;
use function Tempest\uri;

final class ValidationResponseTest extends IntegrationTest
{
    /** @test */
    public function validation_errors_are_listed_in_the_response_body()
    {
        $this->http->post(uri(ValidationController::class), ['number' => 11, 'item.number' => 11]);
    }
}
