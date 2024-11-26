<?php

declare(strict_types=1);

namespace Tempest\Http\Exceptions;

use Tempest\Core\ErrorHandler;
use Throwable;

final class HttpProductionErrorHandler implements ErrorHandler
{
    public function handleException(Throwable $throwable): void
    {
        ll($throwable);

        $this->showErrorPage();
    }

    public function handleError(int $errNo, string $errstr, string $errFile, int $errLine): void
    {
        ll("{$errFile}:{$errLine} {$errstr} ({$errNo})");

        if (
            $errNo === E_USER_WARNING
            || $errNo === E_DEPRECATED
            || $errNo === E_WARNING
        ) {
            return;
        }

        $this->showErrorPage();
    }

    private function showErrorPage(): never
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
