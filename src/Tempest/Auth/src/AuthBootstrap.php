<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Router\RouteConfig;
use Tempest\Router\Router;

final readonly class AuthBootstrap
{
    public function __construct(
        private RouteConfig $routeConfig,
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function __invoke(): void
    {
        $this->routeConfig->middleware->add(AuthorizerMiddleware::class);
    }
}
