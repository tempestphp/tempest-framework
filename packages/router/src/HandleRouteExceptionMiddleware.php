<?php

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Exceptions\ConvertsToResponse;
use Tempest\Router\Exceptions\RouteBindingFailed;
use Tempest\Validation\Exceptions\ValidationFailed;

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
            return new Invalid($validationException->subject, $validationException->failingRules);
        }
    }
}
