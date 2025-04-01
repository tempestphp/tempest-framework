<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Router\Router;

final readonly class AuthBootstrap
{
    public function __construct(
        private Router $router,
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function __invoke(): void
    {
        $this->router->addMiddleware(AuthorizerMiddleware::class);
    }
}
