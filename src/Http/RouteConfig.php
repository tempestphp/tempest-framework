<?php

namespace Tempest\Http;

final readonly class RouteConfig
{
    public function __construct(
        public array $controllers = [],
    ) {}
}