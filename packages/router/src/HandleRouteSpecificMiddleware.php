<?php

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Core\Middleware;
use Tempest\Core\Priority;
use Tempest\Http\Request;
use Tempest\Http\Response;

#[Priority(Priority::LOWEST)]
final readonly class HandleRouteSpecificMiddleware implements HttpMiddleware
{
    public function __construct(
        private MatchedRoute $matchedRoute,
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $middlewareStack = new Middleware(...$this->matchedRoute->route->middleware);

        $callable = new HttpMiddlewareCallable(fn (Request $request) => $next($request));

        foreach ($middlewareStack->unwrap() as $middlewareClass) {
            $callable = new HttpMiddlewareCallable(function (Request $request) use ($middlewareClass, $callable) {
                /** @var HttpMiddleware $middleware */
                $middleware = $this->container->get($middlewareClass->getName());

                return $middleware($request, $callable);
            });
        }

        return $callable($request);
    }
}