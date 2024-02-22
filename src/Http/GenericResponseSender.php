<?php

namespace Tempest\Http;

final readonly class GenericResponseSender implements ResponseSender
{
    public function send(Response $response): Response
    {
        ob_start();

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

        foreach ($response->getHeaders() as $key => $value) {
            header("{$key}: {$value}");
        }

        /**
         * TODO: Is there a reason to manually set the headers
         * vs this http_response_code helper?
         */
        http_response_code($response->getStatus()->value);
    }

    private function sendContent(Response $response): void
    {
        echo $response->getBody();
    }
}