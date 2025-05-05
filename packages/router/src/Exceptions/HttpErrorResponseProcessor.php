<?php

namespace Tempest\Router\Exceptions;

use Tempest\Http\HttpException;
use Tempest\Http\Response;
use Tempest\Router\ResponseProcessor;

final class HttpErrorResponseProcessor implements ResponseProcessor
{
    public function process(Response $response): Response
    {
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
