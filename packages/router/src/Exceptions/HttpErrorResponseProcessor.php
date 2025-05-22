<?php

namespace Tempest\Router\Exceptions;

use Tempest\Core\AppConfig;
use Tempest\Http\HttpException;
use Tempest\Http\Response;
use Tempest\Router\ResponseProcessor;
use Tempest\Router\RouteConfig;

final readonly class HttpErrorResponseProcessor implements ResponseProcessor
{
    public function __construct(
        private AppConfig $appConfig,
        private RouteConfig $routeConfig,
    ) {}

    public function process(Response $response): Response
    {
        // If the response is not a server or client error, we don't need to
        // handle it. In this case, we simply return back the response.
        if (! $response->status->isServerError() && ! $response->status->isClientError()) {
            return $response;
        }

        // If the response already has a body, it means it is most likely
        // meant to be returned as-is, so we don't have to throw an exception.
        if ($response->body) {
            return $response;
        }

        // During tests, the router is generally configured to not throw HTTP exceptions in order
        // to perform assertions on the responses. In this case, we return the response as is.
        if (! $this->routeConfig->throwHttpExceptions) {
            return $response;
        }

        throw new HttpException(
            status: $response->status,
            cause: $response,
        );
    }
}
