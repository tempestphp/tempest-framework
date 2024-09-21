<?php

declare(strict_types=1);

namespace Tempest\Http;

use Generator;
use Tempest\Http\Responses\Download;
use Tempest\Http\Responses\File;
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
        $body = $response->getBody();

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
}
