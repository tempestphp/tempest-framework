<?php

declare(strict_types=1);

namespace Tempest\Bus;

use ReflectionClass;
use ReflectionMethod;

final readonly class CommandHandler
{
    public function __construct(
        public ReflectionMethod $handler
    ) {
    }

    public function __serialize(): array
    {
        return [
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->handler = new ReflectionMethod(
            objectOrMethod: new ReflectionClass($data['handler_class']),
            method: $data['handler_method'],
        );
    }
}
