<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use ReflectionMethod;
use Tempest\Console\Arguments\ForceArgument;
use Tempest\Console\Arguments\HelpArgument;
use Tempest\Console\Arguments\SilentArgument;
use Tempest\Console\Arguments\NoInteractionArgument;

#[Attribute]
final class ConsoleCommand
{
    public ReflectionMethod $handler;

    public function __construct(
        private readonly ?string $name = null,
        private readonly ?string $description = null,
        private readonly ?array $aliases = [],
        private readonly bool $isDangerous = false,
        private readonly bool $isHidden = false,
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isDangerous(): bool
    {
        return $this->isDangerous;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_dangerous' => $this->isDangerous,
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
            'aliases' => $this->aliases,
            'is_hidden' => $this->isHidden,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->isDangerous = $data['is_dangerous'];
        $this->handler = new ReflectionMethod(
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        );
        $this->aliases = $data['aliases'];
        $this->isHidden = $data['is_hidden'];
    }

    /**
     * @return CommandArguments
     */
    public function getAvailableArguments(): CommandArguments
    {
        $injected = [];
        $arguments = [];

        foreach ($this->injectableArguments() as $flag) {
            $availableParameters = $this->handler->getParameters();

            foreach ($availableParameters as $parameter) {
                /**
                 * In case one of predefined flags matches a user-defined parameter, we skip it.
                 */
                if ($parameter->getName() !== $flag->name) {
                    $injected[$flag->name] = $flag;

                    continue 2;
                }
            }

            if (! $availableParameters) {
                $injected[$flag->name] = $flag;
            }
        }

        foreach ($this->handler->getParameters() as $parameter) {
            $arguments[$parameter->getName()] = Argument::new(
                [$parameter->getName()],
                $parameter->getName(),
                parameter: $parameter,
            );
        }

        return new CommandArguments(
            arguments: $arguments,
            injectedArguments: $injected,
        );
    }

    /**
     * @return InjectedArgument[]
     */
    protected function injectableArguments(): array
    {
        $list = [
            SilentArgument::instance(),
            NoInteractionArgument::instance(),
            HelpArgument::instance(),
        ];

        if ($this->isDangerous()) {
            $list[] = ForceArgument::instance();
        }

        return $list;
    }
}
