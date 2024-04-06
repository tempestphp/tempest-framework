<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use ReflectionMethod;

#[Attribute]
final class ConsoleCommand
{
    public ReflectionMethod $handler;

    public function __construct(
        private readonly ?string $name = null,
        public readonly ?string $description = null,
        /** @var string[] */
        public readonly array $aliases = [],
        /** @var string[] */
        public readonly array $help = [],
    ) {
    }

    public function setHandler(ReflectionMethod $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getName(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->handler->getName() === '__invoke'
            ? strtolower($this->handler->getDeclaringClass()->getShortName())
            : strtolower($this->handler->getDeclaringClass()->getShortName() . ':' . $this->handler->getName());
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
            'aliases' => $this->aliases,
            'help' => $this->help,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->handler = new ReflectionMethod(
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        );
        $this->aliases = $data['aliases'];
        $this->help = $data['help'];
    }

    /**
     * @return array<string, ConsoleInputArgument>
     */
    public function getAvailableArguments(): array
    {
        $arguments = [];

        foreach ($this->handler->getParameters() as $parameter) {
            $arguments[$parameter->getName()] = ConsoleInputArgument::fromParameter($parameter);
        }

        return $arguments;
    }
}
