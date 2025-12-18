<?php

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Exceptions\RouteBindingFailed;

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

    /**
     * Some exceptions are not necessary to be thrown as-is, so we catch them here and convert them to equivalent responses.
     * - `RouteBindingFailed` would require to be handled manually in renderers, it's better DX to simply return a 404.
     */
    private function forward(Request $request, HttpMiddlewareCallable $next): Response
    {
        try {
            return $next($request);
        } catch (RouteBindingFailed) {
            return new NotFound();
        }
    }
}
