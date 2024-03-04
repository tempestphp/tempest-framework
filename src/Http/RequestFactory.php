<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Support\ArrayHelper;

final readonly class RequestFactory
{
    public function make(): GenericRequest
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = Method::tryFrom($_SERVER['REQUEST_METHOD'] ?? '') ?? Method::GET;

        // Convert body dot notation `field.nested` into real arrays
        // TODO: refactor `body` to `data`, and simply merge all data together,
        // we don't care where it comes from
        $body = (new ArrayHelper())->unwrap(match ($method) {
            Method::POST => $_POST,
            default => $_GET,
        });

        return new GenericRequest(
            method: $method,
            uri: $uri,
            body: $body,
            headers: $this->getHeaders(),
        );
    }

    private function getHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[strtolower(str_replace('HTTP_', '', $key))] = $value;
            }
        }

        return $headers;
    }
}
