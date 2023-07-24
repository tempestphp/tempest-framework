<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Interface\CanInitialize;
use Tempest\Interface\Container;
use Tempest\Interface\Request;
use Tempest\Interface\Server;

use function Tempest\map;

use Tempest\Support\ArrayHelper;

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
        $body = (new ArrayHelper())->unwrap($server->getBody());

        $request = map(
            [
                'method' => $server->getMethod(),
                'uri' => $server->getUri(),
                'body' => $body,
                'path' => $path,
                'query' => $query,
                ...$body,
            ],
        )->to($className);

        $container->singleton($className, fn () => $request);

        return $request;
    }
}
