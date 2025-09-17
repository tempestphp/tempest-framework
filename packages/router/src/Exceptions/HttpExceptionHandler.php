<?php

namespace Tempest\Router\Exceptions;

use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\ExceptionReporter;
use Tempest\Core\Kernel;
use Tempest\Http\GenericResponse;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Response;
use Tempest\Http\Session\CsrfTokenDidNotMatch;
use Tempest\Http\Status;
use Tempest\Router\ResponseSender;
use Tempest\Support\Filesystem;
use Tempest\View\GenericView;
use Throwable;

final readonly class HttpExceptionHandler implements ExceptionHandler
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
                $throwable instanceof HttpRequestFailed => $this->renderErrorResponse($throwable->status, $throwable),
                $throwable instanceof CsrfTokenDidNotMatch => $this->renderErrorResponse(Status::UNPROCESSABLE_CONTENT),
                default => $this->renderErrorResponse(Status::INTERNAL_SERVER_ERROR),
            };

            $this->responseSender->send($response);
        } finally {
            $this->kernel->shutdown();
        }
    }

    private function renderErrorResponse(Status $status, ?HttpRequestFailed $exception = null): Response
    {
        return new GenericResponse(
            status: $status,
            body: new GenericView(__DIR__ . '/HttpErrorResponse/error.view.php', [
                'css' => $this->getStyleSheet(),
                'status' => $status->value,
                'title' => $status->description(),
                'message' => $exception?->getMessage() ?: match ($status) {
                    Status::INTERNAL_SERVER_ERROR => 'An unexpected server error occurred',
                    Status::NOT_FOUND => 'This page could not be found on the server',
                    Status::FORBIDDEN => 'You do not have permission to access this page',
                    Status::UNAUTHORIZED => 'You must be authenticated in to access this page',
                    Status::UNPROCESSABLE_CONTENT => 'The request could not be processed due to invalid data',
                    default => null,
                },
            ]),
        );
    }

    private function getStyleSheet(): string
    {
        return Filesystem\read_file(__DIR__ . '/HttpErrorResponse/style.css');
    }
}
