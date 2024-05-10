<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Exceptions;

use App\Controllers\FailController;
use Tempest\Exceptions\HttpExceptionHandler;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\uri;

/**
 * @internal
 * @small
 */
class HttpExceptionHandlerTest extends FrameworkIntegrationTestCase
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
