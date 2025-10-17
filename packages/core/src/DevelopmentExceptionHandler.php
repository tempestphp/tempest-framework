<?php

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Http\ContentType;
use Tempest\Http\Request;
use Tempest\Router\Exceptions\JsonHttpExceptionRenderer;
use Tempest\Router\MatchedRoute;
use Tempest\Router\ResponseSender;
use Throwable;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final readonly class DevelopmentExceptionHandler implements ExceptionHandler
{
    private Run $whoops;

    public function __construct(
        private Container $container,
        private ExceptionReporter $exceptionReporter,
        private JsonHttpExceptionRenderer $jsonHandler,
        private ResponseSender $responseSender,
    ) {
        $this->whoops = new Run();
        $this->whoops->pushHandler($this->createHandler());
    }

    public function handle(Throwable $throwable): void
    {
        $this->exceptionReporter->report($throwable);

        $request = $this->container->get(Request::class);

        match (true) {
            $request->accepts(ContentType::HTML, ContentType::XHTML) => $this->whoops->handleException($throwable),
            $request->accepts(ContentType::JSON) => $this->responseSender->send($this->jsonHandler->render($throwable)),
            default => $this->whoops->handleException($throwable),
        };
    }

    private function createHandler(): HandlerInterface
    {
        $handler = new PrettyPageHandler();

        $handler->addDataTableCallback('Route', function () {
            $route = $this->container->get(MatchedRoute::class);

            if (! $route) {
                return [];
            }

            return [
                'Handler' => $route->route->handler->getDeclaringClass()->getFileName() . ':' . $route->route->handler->getName(),
                'URI' => $route->route->uri,
                'Allowed parameters' => $route->route->parameters,
                'Received parameters' => $route->params,
            ];
        });

        $handler->addDataTableCallback('Request', function () {
            $request = $this->container->get(Request::class);

            return [
                'URI' => $request->uri,
                'Method' => $request->method->value,
                'Headers' => $request->headers->toArray(),
                'Parsed body' => array_filter(array_values($request->body)) ? $request->body : [],
                'Raw body' => $request->raw,
            ];
        });

        return $handler;
    }
}
