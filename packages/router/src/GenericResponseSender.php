<?php

declare(strict_types=1);

namespace Tempest\Router;

use Generator;
use Tempest\Container\Container;
use Tempest\Http\ContentType;
use Tempest\Http\Header;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Download;
use Tempest\Http\Responses\EventStream;
use Tempest\Http\Responses\File;
use Tempest\Http\ServerSentEvent;
use Tempest\Support\Json;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

final readonly class GenericResponseSender implements ResponseSender
{
    public function __construct(
        private Container $container,
        private ViewRenderer $viewRenderer,
    ) {}

    public function send(Response $response): Response
    {
        ob_start();
        $this->sendHeaders($response);

        if ($this->shouldSendContent()) {
            ob_flush();
            $this->sendContent($response);
        }

        ob_end_flush();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        return $response;
    }

    private function sendHeaders(Response $response): void
    {
        // TODO: Handle SAPI/FastCGI
        if (headers_sent()) {
            return;
        }

        foreach ($this->resolveHeaders($response) as $header) {
            header($header);
        }

        http_response_code($response->status->value);
    }

    private function resolveHeaders(Response $response): Generator
    {
        $headers = $response->headers;

        if (is_array($response->body)) {
            $headers[ContentType::HEADER] ??= new Header(ContentType::HEADER);
            $headers[ContentType::HEADER]->add(ContentType::JSON->value);
        }

        foreach ($headers as $key => $header) {
            foreach ($header->values as $value) {
                yield "{$key}: {$value}";
            }
        }
    }

    private function shouldSendContent(): bool
    {
        // The request is resolved dynamically from the container
        // because it's only available via the container at a later point,
        // after the response sender has been constructed (set by the router)
        $request = $this->container->get(Request::class);

        return $request->method !== Method::HEAD;
    }

    private function sendContent(Response $response): void
    {
        if ($response instanceof EventStream) {
            $this->sendEventStream($response);
            return;
        }

        $body = $response->body;

        if ($response instanceof File || $response instanceof Download) {
            readfile($body);
        } elseif (is_array($body)) {
            echo json_encode($body);
        } elseif ($body instanceof View) {
            echo $this->viewRenderer->render($body);
        } else {
            echo $body;
        }

        ob_flush();
    }

    private function sendEventStream(EventStream $response): void
    {
        if (ob_get_level() > 0) {
            ob_end_flush();
        }

        foreach ($response->body as $message) {
            if (connection_aborted()) {
                break;
            }

            $event = 'message';
            $data = Json\encode($message);

            if ($message instanceof ServerSentEvent) {
                $event = $message->event;
                $data = Json\encode($message->data);
            }

            echo "event: {$event}\n";
            echo "data: {$data}";
            echo "\n\n";

            if (ob_get_level() > 0) {
                ob_flush();
            }

            flush();
        }
    }
}
