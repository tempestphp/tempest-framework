<?php

namespace Tempest\Router\Exceptions;

use Tempest\Core\AppConfig;
use Tempest\Http\HttpException;
use Tempest\Http\Response;
use Tempest\Router\ResponseProcessor;

final readonly class HttpErrorResponseProcessor implements ResponseProcessor
{
    public function __construct(
        private AppConfig $appConfig,
    ) {}

    public function process(Response $response): Response
    {
        // Throwing an HttpException during tests would make testing more
        // complex, and is not strictly needed. During development, we
        // don't need exceptions either, since the exception handler
        // is different. For this reason, we skip processing here.
        if (! $this->appConfig->environment->isProduction()) {
            return $response;
        }

        // Don't handle responses that already have a body. This is to avoid
        // interferring with error responses voluntarily thrown in userland.
        if ($response->body) {
            return $response;
        }

        // We throw an exception on server and client errors,
        // which is handled in the HTTP exception handler.
        if ($response->status->isServerError() || $response->status->isClientError()) {
            throw new HttpException(status: $response->status, response: $response);
        }

        return $response;
    }
}
