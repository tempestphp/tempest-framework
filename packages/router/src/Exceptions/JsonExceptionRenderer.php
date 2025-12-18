<?php

namespace Tempest\Router\Exceptions;

use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Core\AppConfig;
use Tempest\Core\Priority;
use Tempest\Http\ContentType;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Session\CsrfTokenDidNotMatch;
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
        private AppConfig $appConfig,
        private Validator $validator,
    ) {}

    public function canRender(Throwable $throwable, Request $request): bool
    {
        return $request->accepts(ContentType::JSON);
    }

    public function render(Throwable $throwable): Response
    {
        return match (true) {
            $throwable instanceof ConvertsToResponse => $throwable->convertToResponse(),
            $throwable instanceof HttpRequestFailed => $this->renderErrorResponse($throwable->status, $throwable),
            $throwable instanceof ValidationFailed => $this->renderValidationErrorResponse($throwable),
            $throwable instanceof AccessWasDenied => $this->renderErrorResponse(Status::FORBIDDEN),
            $throwable instanceof CsrfTokenDidNotMatch => $this->renderErrorResponse(Status::UNPROCESSABLE_CONTENT),
            default => $this->renderErrorResponse(Status::INTERNAL_SERVER_ERROR, $throwable),
        };
    }

    private function renderValidationErrorResponse(ValidationFailed $exception): Response
    {
        $errors = Arr\map_iterable($exception->failingRules, fn (array $failingRulesForField, string $field) => Arr\map_iterable(
            array: $failingRulesForField,
            map: fn (FailingRule $rule) => $this->validator->getErrorMessage($rule, $field),
        ));

        return new Json([
            'message' => Arr\first($errors)[0],
            'errors' => $errors,
        ])
            ->setStatus(Status::UNPROCESSABLE_CONTENT)
            ->addHeader('x-validation', value: encode($errors));
    }

    private function renderErrorResponse(Status $status, ?Throwable $exception = null): Response
    {
        if ($status === Status::NOT_FOUND) {
            return new NotFound();
        }

        $response = [
            'message' => static::getErrorMessage($status, $exception),
        ];

        if ($this->appConfig->environment->isLocal() && $exception !== null) {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }

        return new Json($response)->setStatus($status);
    }

    private static function getErrorMessage(Status $status, ?Throwable $exception = null): ?string
    {
        $message = $exception?->getMessage() ?: match ($status) {
            Status::INTERNAL_SERVER_ERROR => 'An unexpected server error occurred',
            Status::FORBIDDEN => 'You do not have permission to access this endpoint',
            Status::UNAUTHORIZED => 'You must be authenticated in to access this endpoint',
            Status::UNPROCESSABLE_CONTENT => 'The request could not be processed due to invalid data',
            default => $status->description(),
        };

        if ($exception instanceof HttpRequestFailed) {
            $message = is_string($exception->cause?->body)
                ? $exception->cause->body
                : $message;
        }

        return $message;
    }
}
