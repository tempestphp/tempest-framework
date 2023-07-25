<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\Interface\Application;
use Tempest\Interface\Container;
use Tempest\Interface\Request;
use Tempest\Interface\Router;

final readonly class HttpApplication implements Application
{
    public function __construct(private Container $container) {}

    public function run(): void
    {
        $router = $this->container->get(Router::class);
        $request = $this->container->get(Request::class);

        $response = $router->dispatch($request);

        ob_start();

        foreach ($response->getHeaders() as $key => $value) {
            header("{$key}: {$value}");
        }

        echo $response->getBody();

        ob_end_flush();
    }
}
