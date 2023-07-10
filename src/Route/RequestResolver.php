<?php

namespace Tempest\Route;

use Tempest\Interfaces\Container;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Resolver;
use Tempest\Interfaces\Server;

final readonly class RequestResolver implements Resolver
{
    public function canResolve(string $className): bool 
    {
        $interface = class_implements($className)[Request::class] ?? null;
        
        return $interface !== null;
    }

    public function resolve(string $className, Container $container): Request
    {
        $server = $container->get(Server::class);

        /** @var Request $request */
        $request = call_user_func_array("{$className}::new", [
            'method' => $server->getMethod(),
            'uri' => $server->getUri(),
            'body' => $server->getBody(),
        ]);

        foreach ($request->getBody() as $key => $value) {
            if (property_exists($request, $key)) {
                $request->$key = $value;
            }
        }

        $container->singleton($className, fn () => $request);

        return $request;
    }
}