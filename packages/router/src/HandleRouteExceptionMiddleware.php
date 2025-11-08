<?php

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\ContentType;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Responses\NotAcceptable;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Exceptions\ConvertsToResponse;
use Tempest\Router\Exceptions\JsonExceptionRenderer;
use Tempest\Router\Exceptions\RouteBindingFailed;
use Tempest\Validation\Exceptions\ValidationFailed;

#[Priority(Priority::FRAMEWORK - 10)]
final readonly class HandleRouteExceptionMiddleware implements HttpMiddleware
{
    public function __construct(
        private RouteConfig $routeConfig,
        private JsonExceptionRenderer $jsonHandler,
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
        } catch (RouteBindingFailed $routeBindingFailed) {
            return match (true) {
                $request->accepts(ContentType::HTML, ContentType::XHTML) => new NotFound(),
                $request->accepts(ContentType::JSON) => $this->jsonHandler->render($routeBindingFailed),
                default => new NotAcceptable(),
            };
        } catch (ValidationFailed $validationException) {
            return match (true) {
                $request->accepts(ContentType::HTML, ContentType::XHTML) => new Invalid($validationException->subject, $validationException->failingRules),
                $request->accepts(ContentType::JSON) => $this->jsonHandler->render($validationException),
                default => new NotAcceptable(),
            };

            return new Invalid($validationException->subject, $validationException->failingRules);
        }
    }
}
