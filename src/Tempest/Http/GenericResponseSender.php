<?php

declare(strict_types=1);

namespace Tempest\Http;

use Generator;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

final readonly class GenericResponseSender implements ResponseSender
{
    public function __construct(
        private ViewRenderer $viewRenderer,
    ) {
    }

    public function send(Response $response): Response
    {
        ob_start();

        $this->sendHeaders($response);
        ob_flush();

        $this->sendContent($response);
        ob_end_flush();

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

        http_response_code($response->getStatus()->value);
    }

    private function resolveHeaders(Response $response): Generator
    {
        $headers = $response->getHeaders();

        if (is_array($response->getBody())) {
            $headers[ContentType::HEADER] ??= new Header(ContentType::HEADER);
            $headers[ContentType::HEADER]->add(ContentType::JSON->value);
        }

        foreach ($headers as $key => $header) {
            foreach ($header->values as $value) {
                yield "{$key}: {$value}";
            }
        }
    }

    private function sendContent(Response $response): void
    {
        foreach ($this->resolveBody($response) as $content) {
            echo $content;
            ob_flush();
        }
    }

    private function resolveBody(Response $response): Generator
    {
        $body = $response->getBody();
        if ($body instanceof Generator) {
            return $body;
        }

        if (is_array($body)) {
            yield json_encode($body);
        } elseif ($body instanceof View) {
            yield $this->viewRenderer->render($body);
        } else {
            yield $body;
        }
    }
}
