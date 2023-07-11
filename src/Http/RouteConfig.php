<?php

namespace Tempest\Http;

final class RouteConfig
{
    public function __construct(
        public array $controllers = [],
    ) {
    }

    public function addController(string $controllerClass): self
    {
        $this->controllers[] = $controllerClass;

        return $this;
    }
}