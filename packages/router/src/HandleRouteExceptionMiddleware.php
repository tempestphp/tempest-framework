<?php

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\HttpException;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Exceptions\NotFoundException;
use Tempest\Validation\Exceptions\ValidationException;

#[Priority(Priority::FRAMEWORK - 9)]
final class HandleRouteExceptionMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        try {
            return $next($request);
        } catch (NotFoundException) {
            return new NotFound();
        } catch (ValidationException $validationException) {
            return new Invalid($validationException->subject, $validationException->failingRules);
//        } catch(HttpException $httpException) {
            // TODO?
        }
    }
}