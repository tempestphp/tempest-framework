<?php

namespace Tempest\Router;

use ReflectionClass;
use Tempest\Core\Priority;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Exceptions\ConvertsToResponse;
use Tempest\Router\Exceptions\RouteBindingFailed;
use Tempest\Validation\ErrorBag;
use Tempest\Validation\Exceptions\ValidationFailed;
use Throwable;

#[Priority(Priority::FRAMEWORK - 10)]
final readonly class HandleRouteExceptionMiddleware implements HttpMiddleware
{
    public function __construct(
        private RouteConfig $routeConfig,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if ($this->routeConfig->throwHttpExceptions === true) {
            $response = $this->forward($request, $next);

            if ($response->status->isServerError() || $response->status->isClientError()) {
                throw new HttpRequestFailed(
                    request: $request,
                    status: $response->status,
                    cause: $response,
                );
            }

            return $response;
        }

        return $this->forward($request, $next);
    }

    private function forward(Request $request, HttpMiddlewareCallable $next): Response
    {
        try {
            return $next($request);
        } catch (ConvertsToResponse $convertsToResponse) {
            return $convertsToResponse->toResponse();
        } catch (RouteBindingFailed) {
            return new NotFound();
        } catch (ValidationFailed $validationException) {
            $errorBag = $this->resolveErrorBag($validationException->subject, $request);

            return new Invalid(
                $request,
                $validationException->failingRules,
                $errorBag,
            );
        }
    }

    private function resolveErrorBag(mixed $subject, Request $request): ?string
    {
        if (isset($request->body['__error_bag'])) {
            return $request->body['__error_bag'];
        }

        if (! is_object($subject) && ! is_string($subject)) {
            return null;
        }

        try {
            $reflectionClass = new ReflectionClass($subject);
            $attributes = $reflectionClass->getAttributes(ErrorBag::class);

            if (! empty($attributes)) {
                $errorBag = $attributes[0]->newInstance();
                return $errorBag->name;
            }
        } catch (Throwable) {
            // If reflection fails, just return null
        }

        return null;
    }
}
