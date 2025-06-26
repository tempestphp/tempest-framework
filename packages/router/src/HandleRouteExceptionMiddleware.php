<?php

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Exceptions\NotFoundException;
use Tempest\Validation\Exceptions\ValidationException;

#[Priority(Priority::FRAMEWORK - 10)]
final readonly class HandleRouteExceptionMiddleware implements HttpMiddleware
{
    public function __construct(
        private RouteConfig $routeConfig,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if ($this->routeConfig->throwHttpExceptions === true) {
            return $next($request);
        }

        try {
            return $next($request);
        } catch (NotFoundException) {
            return new NotFound();
        } catch (ValidationException $validationException) {
            return new Invalid($validationException->subject, $validationException->failingRules);
        }
    }
}
