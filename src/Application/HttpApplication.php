<?php

namespace Tempest\Application;

use Tempest\Interfaces\Application;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Router;

final readonly class HttpApplication implements Application
{
    public function __construct(
        private Request $request,
        private Router $router,
    ) {
    }

    public function run(): void
    {
        $response = $this->router->dispatch($this->request);

        ob_start();

        foreach ($response->getHeaders() as $key => $value) {
            header("{$key}: {$value}");
        }

        echo $response->getBody();

        ob_end_flush();
    }
}
