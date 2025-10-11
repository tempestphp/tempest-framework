<?php

namespace Tempest\Router\Exceptions;

use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\ExceptionReporter;
use Tempest\Core\Kernel;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Http\Session\CsrfTokenDidNotMatch;
use Tempest\Http\Status;
use Tempest\Router\ResponseSender;
use Throwable;

use function Tempest\Support\arr;

final readonly class JsonHttpExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private AppConfig $appConfig,
        private Kernel $kernel,
        private ResponseSender $responseSender,
        private Container $container,
        private ExceptionReporter $exceptionReporter,
    ) {}

    public function handle(Throwable $throwable): void
    {
        try {
            $this->exceptionReporter->report($throwable);

            $response = match (true) {
                $throwable instanceof ConvertsToResponse => $throwable->toResponse(),
                $throwable instanceof AccessWasDenied => $this->renderErrorResponse(Status::FORBIDDEN),
                $throwable instanceof HttpRequestFailed => $this->renderErrorResponse($throwable->status),
                $throwable instanceof CsrfTokenDidNotMatch => $this->renderErrorResponse(Status::UNPROCESSABLE_CONTENT),
                default => $this->renderErrorResponse(
                    Status::INTERNAL_SERVER_ERROR,
                    $this->appConfig->environment->isLocal() ? $throwable : null,
                ),
            };

            $this->responseSender->send($response);
        } finally {
            $this->kernel->shutdown();
        }
    }

    private function renderErrorResponse(Status $status, ?Throwable $exception = null): Response
    {
        return new Json(
            $this->appConfig->environment->isLocal() && $exception !== null
                ? [
                    'message' => $exception->getMessage(),
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => arr($exception->getTrace())->map(
                        fn ($trace) => arr($trace)->removeKeys('args')->toArray(),
                    )->toArray(),
                ] : [
                    'message' => static::getErrorMessage($status, $exception),
                ],
        )->setStatus($status);
    }

    private static function getErrorMessage(Status $status, ?Throwable $exception = null): ?string
    {
        return (
            $exception?->getMessage() ?: match ($status) {
                Status::INTERNAL_SERVER_ERROR => 'An unexpected server error occurred',
                Status::NOT_FOUND => 'This page could not be found on the server',
                Status::FORBIDDEN => 'You do not have permission to access this page',
                Status::UNAUTHORIZED => 'You must be authenticated in to access this page',
                Status::UNPROCESSABLE_CONTENT => 'The request could not be processed due to invalid data',
                default => null,
            }
        );
    }
}
