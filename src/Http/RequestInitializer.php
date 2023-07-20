<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Interfaces\CanInitialize;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Server;

use function Tempest\map;

final readonly class RequestInitializer implements CanInitialize
{
    public function canInitialize(string $className): bool
    {
        return is_a($className, Request::class, true);
    }

    public function initialize(string $className, Container $container): Request
    {
        $server = $container->get(Server::class);

        if ($className === Request::class) {
            $className = GenericRequest::class;
        }

        $decodedUri = rawurldecode($server->getUri());
        $parsedUrl = parse_url($decodedUri);

        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? null;

        $request = map(
            [
                'method' => $server->getMethod(),
                'uri' => $server->getUri(),
                'body' => $server->getBody(),
                'path' => $path,
                'query' => $query,
                ...$server->getBody(),
            ],
        )->to($className);

        $container->singleton($className, fn () => $request);

        return $request;
    }
}
