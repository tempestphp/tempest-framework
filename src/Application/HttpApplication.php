<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\Container\Container;
use Tempest\Http\Request;
use Tempest\Http\ResponseSender;
use Tempest\Http\Router;

final readonly class HttpApplication implements Application
{
    public function __construct(private Container $container)
    {
    }

    public function run(): void
    {
        $this->container->singleton(Application::class, fn () => $this);

        $router = $this->container->get(Router::class);
        $request = $this->container->get(Request::class);
        $responseSender = $this->container->get(ResponseSender::class);

        $responseSender->send(
            $router->dispatch($request)
        );
    }
}
