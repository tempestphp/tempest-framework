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
        $server = $container->get(Server::class);

        $className = $this->className;

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
