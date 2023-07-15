<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Interfaces\CanInitialize;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Server;

final readonly class RequestInitializer implements CanInitialize
{
    public function canInitialize(string $className): bool
    {
        $interface = class_implements($className)[Request::class] ?? null;

        return $interface !== null || $className === Request::class;
    }

    public function initialize(string $className, Container $container): Request
    {
        $server = $container->get(Server::class);

        if ($className === Request::class) {
            $className = GenericRequest::class;
        }

        /** @var Request $request */
        $request = new $className(
            method: $server->getMethod(),
            uri: $server->getUri(),
            body: $server->getBody(),
        );

        foreach ($request->getBody() as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (property_exists($request, $key)) {
                $request->$key = $value;
            }
        }

        $container->singleton($className, fn () => $request);

        return $request;
    }
}
