<?php

namespace Tempest\Router\Exceptions;

use Tempest\Container\Container;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\Exceptions\ExceptionProcessor;
use Tempest\Core\Kernel;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Router\ResponseSender;
use Tempest\Router\RouteConfig;
use Tempest\Support\Arr;
use Throwable;

final readonly class HttpExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private ResponseSender $responseSender,
        private Kernel $kernel,
        private Container $container,
        private ExceptionProcessor $exceptionProcessor,
        private RouteConfig $routeConfig,
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
        ksort($this->routeConfig->exceptionRenderers);

        foreach (Arr\flatten($this->routeConfig->exceptionRenderers) as $rendererClass) {
            /** @var ExceptionRenderer $renderer */
            $renderer = $this->container->get($rendererClass);

            if ($renderer->canRender($throwable, $request)) {
                return $renderer->render($throwable);
            }
        }

        return new GenericResponse(status: Status::NOT_ACCEPTABLE);
    }
}
