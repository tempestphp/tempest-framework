<?php

namespace Tempest\Router\Exceptions;

use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Core\Environment;
use Tempest\Core\Priority;
use Tempest\Http\ContentType;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tempest\Support\Arr;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Validator;
use Throwable;

use function Tempest\Support\Json\encode;

#[Priority(Priority::LOWEST)]
final readonly class JsonExceptionRenderer implements ExceptionRenderer
{
    public function __construct(
        private Environment $environment,
        private Validator $validator,
    ) {}

    public function canRender(Throwable $throwable, Request $request): bool
    {
        return $request->accepts(ContentType::JSON);
    }

    public function render(Throwable $throwable): Response
    {
        return match (true) {
            $throwable instanceof ValidationFailed => $this->renderValidationFailedResponse($throwable),
            $throwable instanceof AccessWasDenied => $this->renderErrorResponse(Status::FORBIDDEN, message: $throwable->accessDecision->message),
            $throwable instanceof HttpRequestFailed => $this->renderHttpRequestFailed($throwable),
            $throwable instanceof ConvertsToResponse => $throwable->convertToResponse(),
            default => $this->renderErrorResponse(Status::INTERNAL_SERVER_ERROR, throwable: $throwable),
        };
    }

    private function renderHttpRequestFailed(HttpRequestFailed $exception): Response
    {
        if ($exception->getMessage() !== '') {
            return $this->renderErrorResponse($exception->status, message: $exception->getMessage());
        }

        if ($exception->cause && is_string($exception->cause->body)) {
            return $this->renderErrorResponse($exception->status, message: $exception->cause->body);
        }

        if ($exception->cause && $exception->cause->body) {
            return $exception->cause;
        }

        return $this->renderErrorResponse($exception->status, throwable: $exception);
    }

    private function renderValidationFailedResponse(ValidationFailed $exception): Response
    {
        $errors = Arr\map_iterable($exception->failingRules, fn (array $failingRulesForField, string $field) => Arr\map_iterable(
            array: $failingRulesForField,
            map: fn (FailingRule $rule) => $this->validator->getErrorMessage($rule, $field),
        ));

        return new Json(
            body: [
                'message' => Arr\first($errors)[0],
                'errors' => $errors,
            ],
            status: Status::UNPROCESSABLE_CONTENT,
            headers: ['x-validation' => encode($errors)],
        );
    }

    private function renderErrorResponse(Status $status, ?string $message = null, ?Throwable $throwable = null): Response
    {
        if ($status === Status::NOT_FOUND) {
            return new NotFound();
        }

        $body = [
            'message' => $message ?? $status->description(),
        ];

        if ($this->environment->isLocal() && $throwable !== null) {
            $body['debug'] = array_filter([
                'message' => $throwable->getMessage(),
                'exception' => get_class($throwable),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => Arr\map_iterable(
                    array: $throwable->getTrace(),
                    map: fn (array $trace) => Arr\remove_keys($trace, 'args'),
                ),
            ]);
        }

        return new Json(
            body: $body,
            status: $status,
        );
    }
}
