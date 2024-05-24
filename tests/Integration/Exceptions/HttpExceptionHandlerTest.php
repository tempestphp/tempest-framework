<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Exceptions;

use App\Controllers\FailController;
use Tempest\Exceptions\HttpExceptionHandler;
use function Tempest\uri;
use Tests\Tempest\Integration\FrameworkIntegrationTest;

/**
 * @internal
 * @small
 */
class HttpExceptionHandlerTest extends FrameworkIntegrationTest
{
    public function test_exception()
    {
        $app = $this->actAsHttpApplication();
        $this->appConfig->enableExceptionHandling = true;
        $this->appConfig->exceptionHandlers = [
            $this->container->get(HttpExceptionHandler::class),
        ];

        $_SERVER['REQUEST_URI'] = uri(FailController::class);

        ob_start();
        $app->run();
        $contents = ob_get_clean();

        $this->assertStringContainsString('<title>Nope</title>', $contents);
    }
}
