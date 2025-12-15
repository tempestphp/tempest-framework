<?php

namespace Tempest\Router\Exceptions;

use Tempest\Container\Container;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\Exceptions\ExceptionProcessor;
use Tempest\Core\Kernel;
use Tempest\Http\ContentType;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Router\ResponseSender;
use Tempest\Router\RouteConfig;
use Throwable;

final readonly class HttpExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private ResponseSender $responseSender,
        private Kernel $kernel,
        private Container $container,
        private ExceptionProcessor $exceptionProcessor,
        private RouteConfig $routeConfig,
        private JsonExceptionRenderer $jsonHandler,
        private HtmlExceptionRenderer $htmlHandler,
    ) {}

    public function handle(Throwable $throwable): void
    {
        $request = $this->container->get(Request::class);

        try {
            $this->exceptionProcessor->process($throwable);
            $this->responseSender->send($this->renderResponse($request, $throwable));
        } finally {
            $this->kernel->shutdown();
        }
    }

    public function renderResponse(Request $request, Throwable $throwable): Response
    {
        // Sort by priority ascending (HIGHEST = 0 checked first, LOWEST = 10000 checked last)
        ksort($this->routeConfig->exceptionRenderers);

        foreach ($this->routeConfig->exceptionRenderers as $rendererClass) {
            /** @var ExceptionRenderer $renderer */
            $renderer = $this->container->get($rendererClass);

            if ($renderer->canRender($throwable, $request)) {
                return $renderer->render($throwable);
            }
        }

        // Fall back to default renderers
        if ($this->htmlHandler->canRender($throwable, $request)) {
            return $this->htmlHandler->render($throwable);
        }

        if ($this->jsonHandler->canRender($throwable, $request)) {
            return $this->jsonHandler->render($throwable);
        }

        return new GenericResponse(status: Status::NOT_ACCEPTABLE);
    }
}
