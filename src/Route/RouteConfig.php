<?php

namespace Tempest\Route;

final readonly class RouteConfig
{
    public function __construct(
        public array $controllers = [],
    ) {}
}