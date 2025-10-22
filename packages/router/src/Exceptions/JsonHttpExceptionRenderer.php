<?php

namespace Tempest\Router\Exceptions;

use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Core\AppConfig;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Http\Session\CsrfTokenDidNotMatch;
use Tempest\Http\Status;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\Rule;
use Tempest\Validation\Validator;
use Throwable;

use function Tempest\Support\arr;

final readonly class JsonHttpExceptionRenderer
{
    public function __construct(
        private AppConfig $appConfig,
        private Validator $validator,
    ) {}

    public function render(Throwable $throwable): Response
    {
        return match (true) {
            $throwable instanceof ConvertsToResponse => $throwable->toResponse(),
            $throwable instanceof ValidationFailed => $this->renderValidationErrorResponse($throwable),
            $throwable instanceof RouteBindingFailed => $this->renderErrorResponse(Status::NOT_FOUND),
            $throwable instanceof AccessWasDenied => $this->renderErrorResponse(Status::FORBIDDEN),
            $throwable instanceof HttpRequestFailed => $this->renderErrorResponse($throwable->status, $throwable),
            $throwable instanceof CsrfTokenDidNotMatch => $this->renderErrorResponse(Status::UNPROCESSABLE_CONTENT),
            default => $this->renderErrorResponse(Status::INTERNAL_SERVER_ERROR, $throwable),
        };
    }

    private function renderValidationErrorResponse(ValidationFailed $exception): Response
    {
        $errors = arr($exception->failingRules)->map(
            fn (array $failingRulesForField, string $field) => arr($failingRulesForField)->map(
                fn (Rule $rule) => $this->validator->getErrorMessage($rule, $field),
            )->toArray(),
        );

        return new Json([
            'message' => $errors->first()[0],
            'errors' => $errors->toArray(),
        ])->setStatus(Status::UNPROCESSABLE_CONTENT);
    }

    private function renderErrorResponse(Status $status, ?Throwable $exception = null): Response
    {
        return new Json(
            $this->appConfig->environment->isLocal() && $exception !== null
                ? [
                    'message' => static::getErrorMessage($status, $exception),
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
                default => $status->description(),
            }
        );
    }
}
