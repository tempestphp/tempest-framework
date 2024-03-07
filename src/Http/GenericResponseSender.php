<?php

declare(strict_types=1);

namespace Tempest\Http;

final readonly class GenericResponseSender implements ResponseSender
{
    public function send(Response $response): Response
    {
        ob_start();

        $response = $this->prepareResponse($response);

        $this->sendHeaders($response);
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

        foreach ($response->getHeaders() as $key => $values) {
            foreach ($values as $value) {
                header("{$key}: {$value}");
            }
        }

        http_response_code($response->getStatus()->value);
    }

    private function sendContent(Response $response): void
    {
        echo $response->getBody();
    }

    private function prepareResponse(Response $response): Response
    {
        $body = $response->getBody();

        if (is_array($body)) {
            $response->header('Content-Type', 'application/json');
            $response->body(json_encode($body));
        }

        return $response;
    }
}
