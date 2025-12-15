<?php

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\Exceptions\ConvertsToResponse;

#[Priority(Priority::FRAMEWORK - 10)]
final readonly class HandleRouteExceptionMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $this->forward($request, $next);

        if (! $response->status->isClientError() && ! $response->status->isServerError()) {
            return $response;
        }

        throw new HttpRequestFailed(
            status: $response->status,
            cause: $response,
            request: $request,
        );
    }

    private function forward(Request $request, HttpMiddlewareCallable $next): Response
    {
        try {
            return $next($request);
        } catch (ConvertsToResponse $convertsToResponse) {
            return $convertsToResponse->toResponse();
        }
    }
}
