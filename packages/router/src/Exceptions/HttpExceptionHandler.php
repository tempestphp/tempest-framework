<?php

namespace Tempest\Router\Exceptions;

use Tempest\Container\Container;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\ExceptionReporter;
use Tempest\Core\Kernel;
use Tempest\Http\ContentType;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Router\ResponseSender;
use Throwable;

final readonly class HttpExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private ResponseSender $responseSender,
        private Kernel $kernel,
        private Container $container,
        private ExceptionReporter $exceptionReporter,
        private JsonExceptionRenderer $jsonHandler,
        private HtmlExceptionRenderer $htmlHandler,
    ) {}

    public function handle(Throwable $throwable): void
    {
        $request = $this->container->get(Request::class);

        try {
            $this->exceptionReporter->report($throwable);
            $this->responseSender->send($this->renderResponse($request, $throwable));
        } finally {
            $this->kernel->shutdown();
        }
    }

    public function renderResponse(Request $request, Throwable $throwable): Response
    {
        return match (true) {
            $request->accepts(ContentType::HTML, ContentType::XHTML) => $this->htmlHandler->render($throwable),
            $request->accepts(ContentType::JSON) => $this->jsonHandler->render($throwable),
            default => new GenericResponse(status: Status::NOT_ACCEPTABLE),
        };
    }
}
