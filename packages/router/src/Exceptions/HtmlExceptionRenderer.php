<?php

namespace Tempest\Router\Exceptions;

use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Container\Container;
use Tempest\Core\Environment;
use Tempest\Core\Priority;
use Tempest\Http\ContentType;
use Tempest\Http\GenericResponse;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\SensitiveField;
use Tempest\Http\Session\FormSession;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Intl\Translator;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr;
use Tempest\Support\Filesystem;
use Tempest\Support\Json;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Validator;
use Tempest\View\GenericView;
use Throwable;

#[Priority(Priority::LOW)]
final readonly class HtmlExceptionRenderer implements ExceptionRenderer
{
    public function __construct(
        private Environment $environment,
        private Translator $translator,
        private Request $request,
        private Validator $validator,
        private Container $container,
    ) {}

    public function canRender(Throwable $throwable, Request $request): bool
    {
        return $request->accepts(ContentType::HTML, ContentType::XHTML);
    }

    public function render(Throwable $throwable): Response
    {
        $response = match (true) {
            $throwable instanceof ValidationFailed => $this->renderValidationFailedResponse($throwable),
            $throwable instanceof AccessWasDenied => $this->renderErrorResponse(Status::FORBIDDEN, message: $throwable->accessDecision->message),
            $throwable instanceof HttpRequestFailed => $this->renderHttpRequestFailed($throwable),
            $throwable instanceof ConvertsToResponse => $throwable->convertToResponse(),
            default => $this->renderErrorResponse(Status::INTERNAL_SERVER_ERROR),
        };

        if ($this->shouldRenderDevelopmentException($throwable)) {
            return new DevelopmentException(
                throwable: $throwable,
                response: $response,
                request: $this->request,
            );
        }

        return $response;
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

        return $this->renderErrorResponse($exception->status);
    }

    private function renderErrorResponse(Status $status, ?string $message = null): Response
    {
        return new GenericResponse(
            status: $status,
            body: new GenericView(__DIR__ . '/production/error.view.php', [
                'css' => $this->getStyleSheet(),
                'status' => $status->value,
                'title' => $status->description(),
                'message' => $message ?? $this->translator->translate("http_status_error.{$status->value}"),
            ]),
        );
    }

    private function getStyleSheet(): string
    {
        return Filesystem\read_file(__DIR__ . '/production/style.css');
    }

    private function shouldRenderDevelopmentException(Throwable $throwable): bool
    {
        if (! $this->environment->isLocal()) {
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

    private function renderValidationFailedResponse(ValidationFailed $exception): Response
    {
        $status = Status::UNPROCESSABLE_CONTENT;
        $headers = [];

        if ($referer = $this->request->headers->get('referer')) {
            $headers['Location'] = $referer;
            $status = Status::FOUND;
        }

        if ($this->container->has(Session::class)) {
            $this->container->get(FormSession::class)->setErrors($exception->failingRules);
            $this->container->get(FormSession::class)->setOriginalValues($this->filterSensitiveFields($this->request, $exception->targetClass));
        }

        $errors = Arr\map_iterable($exception->failingRules, fn (array $failingRulesForField, string $field) => Arr\map_iterable(
            array: $failingRulesForField,
            map: fn (FailingRule $rule) => $this->validator->getErrorMessage($rule, $field),
        ));

        $headers['x-validation'] = Json\encode($errors);

        return new GenericResponse(
            status: $status,
            headers: $headers,
        );
    }

    /**
     * @param class-string|null $targetClass
     */
    private function filterSensitiveFields(Request $request, ?string $targetClass): array
    {
        $body = $request->body;

        if ($targetClass === null) {
            return $body;
        }

        $reflector = new ClassReflector($targetClass);

        foreach ($reflector->getPublicProperties() as $property) {
            if ($property->hasAttribute(SensitiveField::class)) {
                unset($body[$property->getName()]);
            }
        }

        return $body;
    }
}
