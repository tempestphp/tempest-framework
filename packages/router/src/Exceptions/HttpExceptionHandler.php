<?php

namespace Tempest\Router\Exceptions;

use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\ExceptionReporter;
use Tempest\Core\Kernel;
use Tempest\Http\GenericResponse;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Router\ResponseSender;
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
                $throwable instanceof RouteBindingFailed => $this->renderErrorResponse(Status::NOT_FOUND),
                $throwable instanceof HttpRequestFailed => $this->renderErrorResponse($throwable->status, $throwable),
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
                'message' =>
                    $exception?->getMessage() ?: match ($status) {
                        Status::INTERNAL_SERVER_ERROR => 'An unexpected server error occurred',
                        Status::NOT_FOUND => 'This page could not be found on the server',
                        Status::FORBIDDEN => 'You do not have permission to access this page',
                        Status::UNAUTHORIZED => 'You must be authenticated in to access this page',
                        default => null,
                    }
                ,
            ]),
        );
    }

    private function getStyleSheet(): string
    {
        return file_get_contents(__DIR__ . '/HttpErrorResponse/style.css');
    }
}
