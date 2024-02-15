<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;
use ReflectionMethod;

#[Attribute]
class Route
{
    public ReflectionMethod $handler;

    public function __construct(
        public string $uri,
        public Method $method,
    ) {
    }

    public function setHandler(ReflectionMethod $handler): self
    {
        $this->handler = $handler;

        return $this;
    }
}
