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

    public function __serialize(): array
    {
        return [
            'uri' => $this->uri,
            'method' => $this->method,
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->uri = $data['uri'];
        $this->method = $data['method'];
        $this->handler = new ReflectionMethod(
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        );
    }
}
