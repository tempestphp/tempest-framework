<?php

declare(strict_types=1);

namespace Tempest\Core;

use NunoMaduro\Collision\Provider as Collision;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final readonly class GenericExceptionHandlerSetup implements ExceptionHandlerSetup
{
    public function setup(AppConfig $appConfig): void
    {
        if ($appConfig->environment->isTesting()) {
            return;
        }

        // Console
        if ($_SERVER['argv'] ?? null) {
            (new Collision())->register();

            return;
        }

        // Production web
        if ($appConfig->environment->isProduction()) {
            set_exception_handler($this->renderErrorPage(...));
            /** @phpstan-ignore-next-line  */
            set_error_handler($this->renderErrorPage(...));

            return;
        }

        // Local web
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();
    }

    public function renderErrorPage(): void
    {
        ob_start();

        if (! headers_sent()) {
            http_response_code(500);
        }

        echo file_get_contents(__DIR__ . '/500.html');

        ob_end_flush();

        exit;
    }
}
