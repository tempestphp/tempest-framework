<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\RequiresClassName;
use function Tempest\map;
use Tempest\Support\ArrayHelper;

final class RequestInitializer implements Initializer, CanInitialize, RequiresClassName
{
    private string $className;

    public function canInitialize(string $className): bool
    {
        return is_a($className, Request::class, true);
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function initialize(Container $container): Request
    {
        $className = $this->className === Request::class
            ? GenericRequest::class
            : $this->className;

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $decodedUri = rawurldecode($uri);
        $parsedUrl = parse_url($decodedUri);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? null;
        $method = Method::tryFrom($_SERVER['REQUEST_METHOD'] ?? '') ?? Method::GET;
        // Convert body dot notation `field.nested` into real arrays
        // TODO: we might consider moving this logic to the ORM instead
        $body = (new ArrayHelper())->unwrap(match ($method) {
            Method::POST => $_POST,
            default => $_GET,
        });

        $request = map(
            [
                'method' => $method,
                'uri' => $uri,
                'body' => $body,
                'headers' => $this->getHeaders(),
                'path' => $path,
                'query' => $query,
                ...$body,
            ],
        )->to($className);

        $container->singleton($className, fn () => $request);

        return $request;
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
