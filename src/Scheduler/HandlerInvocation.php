<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use ReflectionMethod;

final readonly class HandlerInvocation implements Invocation
{
    public function __construct(
        public ReflectionMethod $handler,
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
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        );
    }

    public function getName(): string
    {
        $handlerDeclaration = $this->handler->getDeclaringClass()->getName() . '::' . $this->handler->getName();

        return str_replace("\\", "\\\\", $handlerDeclaration);
    }
}
