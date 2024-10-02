<?php

namespace Tempest\Auth;

use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Http\Router;

final readonly class AuthBootstrap
{
    public function __construct(
        private Router $router
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function __invoke(): void
    {
        $this->router->addMiddleware(AuthorizeMiddleware::class);
    }
}