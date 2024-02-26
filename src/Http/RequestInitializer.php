<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\RequiresClassName;
use Tempest\Mapper\ObjectMapper;

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

    // TODO: We have a lot of work to do here, but this gets us up and running.
    public function initialize(Container $container): Request
    {
        $className = $this->className;

        if ($className === Request::class) {
            $className = GenericRequest::class;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $decodedUri = rawurldecode($uri);
        $parsedUrl = parse_url($decodedUri);

        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? null;
        $body = file_get_contents('php://input');

        $request = $container->get(ObjectMapper::class)->withData(            [
            'method' => isset($_SERVER['REQUEST_METHOD']) ? Method::tryFrom($_SERVER['REQUEST_METHOD']) : Method::GET,
            'uri' => $uri,
            'body' => $body,
            'path' => $path,
            'query' => $query,
        ])->to($className);

        $container->singleton(Request::class, fn () => $request);

        return $request;
    }
}
