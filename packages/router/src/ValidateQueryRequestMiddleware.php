<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\ContentType;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;

final readonly class ValidateQueryRequestMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if ($request->method !== Method::QUERY) {
            return $next($request);
        }

        $contentType = trim($request->headers->get(ContentType::HEADER, ''));

        if ($contentType === '') {
            throw new HttpRequestFailed(Status::BAD_REQUEST);
        }

        $mediaType = strtolower(trim(explode(';', $contentType, 2)[0]));
        $isJson = $mediaType === ContentType::JSON->value || str_ends_with($mediaType, '+json');

        $hasInvalidJson = $request->raw !== null && ($request->raw === '' ? $request->body === [] : ! json_validate($request->raw));

        if ($isJson && $hasInvalidJson) {
            throw new HttpRequestFailed(Status::BAD_REQUEST);
        }

        return $next($request);
    }
}
