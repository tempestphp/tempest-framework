<?php

declare(strict_types=1);

namespace Tempest\Core;

use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final readonly class GenericExceptionHandlerSetup implements ExceptionHandlerSetup
{
    public function setup(AppConfig $appConfig): void
    {
        // Production web
        if ($appConfig->environment->isProduction()) {
            set_exception_handler($this->renderExceptionPage(...));
            /** @phpstan-ignore-next-line */
            set_error_handler($this->renderErrorPage(...));

            return;
        }

        // Local web
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();
    }

    public function renderExceptionPage(Throwable $throwable): void
    {
        ll($throwable);

        ob_start();

        if (! headers_sent()) {
            http_response_code(500);
        }

        echo file_get_contents(__DIR__ . '/500.html');

        ob_end_flush();

        exit;
    }

}
