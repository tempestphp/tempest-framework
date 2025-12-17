<?php

namespace Tempest\Router\Exceptions;

use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\Priority;
use Tempest\Http\ContentType;
use Tempest\Http\GenericResponse;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Session\CsrfTokenDidNotMatch;
use Tempest\Http\Status;
use Tempest\Support\Filesystem;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\View\GenericView;
use Throwable;

/**
 * Renders exceptions for HTML content. The priority is lowered by one because
 * JSON-rendering should be the default for requests without `Accept` header.
 */
#[Priority(Priority::LOWEST + 1)]
final readonly class HtmlExceptionRenderer implements ExceptionRenderer
{
    public function __construct(
        private AppConfig $appConfig,
        private Container $container,
    ) {}

    public function canRender(Throwable $throwable, Request $request): bool
    {
        return $request->accepts(ContentType::HTML, ContentType::XHTML);
    }

    public function render(Throwable $throwable): Response
    {
        if ($throwable instanceof ConvertsToResponse) {
            return $throwable->toResponse();
        }

        $response = match (true) {
            $throwable instanceof ConvertsToResponse => $throwable->toResponse(),
            $throwable instanceof ValidationFailed => new Invalid($throwable->subject, $throwable->failingRules, $throwable->targetClass),
            $throwable instanceof AccessWasDenied => $this->renderErrorResponse(Status::FORBIDDEN),
            $throwable instanceof HttpRequestFailed => $this->renderErrorResponse($throwable->status, $throwable),
            $throwable instanceof CsrfTokenDidNotMatch => $this->renderErrorResponse(Status::UNPROCESSABLE_CONTENT),
            default => $this->renderErrorResponse(Status::INTERNAL_SERVER_ERROR, $throwable),
        };

        if ($this->shouldRenderDevelopmentException($throwable)) {
            return new DevelopmentException(
                throwable: $throwable,
                response: $response,
                request: $this->container->get(Request::class),
            );
        }

        return $response;
    }

    private function renderErrorResponse(Status $status, ?Throwable $exception = null): Response
    {
        if ($exception instanceof HttpRequestFailed && $exception->cause?->body) {
            return $exception->cause;
        }

        return new GenericResponse(
            status: $status,
            body: new GenericView(__DIR__ . '/production/error.view.php', [
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
        return Filesystem\read_file(__DIR__ . '/production/style.css');
    }

    private function shouldRenderDevelopmentException(Throwable $throwable): bool
    {
        if (! $this->appConfig->environment->isLocal()) {
            return false;
        }

        if (! $throwable instanceof HttpRequestFailed) {
            return true;
        }

        if ($throwable->status === Status::NOT_FOUND) {
            return false;
        }

        return true;
    }
}
