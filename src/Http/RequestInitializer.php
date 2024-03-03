<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use function Tempest\map;
use Tempest\Support\ArrayHelper;

final class RequestInitializer implements DynamicInitializer
{
    public function canInitialize(string $className): bool
    {
        return is_a($className, Request::class, true);
    }

    public function initialize(string $className, Container $container): Request
    {
        $className = $className === Request::class
            ? GenericRequest::class
            : $className;

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = Method::tryFrom($_SERVER['REQUEST_METHOD'] ?? '') ?? Method::GET;

        // Convert body dot notation `field.nested` into real arrays
        // TODO: refactor `body` to `data`, and simply merge all data together,
        // we don't care where it comes from
        $body = (new ArrayHelper())->unwrap(match ($method) {
            Method::POST => $_POST,
            default => $_GET,
        });

        $request = new GenericRequest(
            method: $method,
            uri: $uri,
            body: $body,
            headers: $this->getHeaders(),
        );

        // If we need something more specific, we'll map our GenericRequest to the specific Request class
        if ($className !== GenericRequest::class) {
            $request = map($request)->to($className);
        }

        // We register the request as a singleton in the container
        // for both its concrete classname and the interface
        $container->singleton(Request::class, fn () => $request);
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
