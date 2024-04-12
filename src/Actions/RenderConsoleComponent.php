<?php

namespace Tempest\Console\Actions;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\Console;
use Tempest\Console\ConsoleComponent;
use Tempest\Console\HandlesKey;
use Tempest\Console\Terminal\InteractiveTerminal;
use Tempest\Support\Reflection\Attributes;

final readonly class RenderConsoleComponent
{
    public function __construct(private Console $console) {}

    public function __invoke(ConsoleComponent $component): mixed
    {
        $terminal = new InteractiveTerminal($this->console);

        [$keyBindings, $inputHandlers] = $this->resolveHandlers($component);

        $result = $terminal->render($component);

        if ($result) {
            return $result;
        }

        while ($key = $this->console->read(16)) {
            $return = null;

            if ($handlersForKey = $keyBindings[$key] ?? null) {
                // Specific key handlers
                foreach ($handlersForKey as $handler) {
                    $return ??= $handler->invoke($component, $this);
                }
            } else {
                // Catch-all key handlers
                foreach ($inputHandlers as $handler) {
                    $return ??= $handler->invoke($component, $key, $this);
                }
            }

            // If a handler returned a result, we'll return
            if ($return) {
                $terminal->switchToNormalMode();

                return $return;
            }

            // Rerender the component
            $terminal->render($component);
        }
    }

    private function resolveHandlers(ConsoleComponent $component): array
    {
        /** @var ReflectionMethod[][] $keyBindings */
        $keyBindings = [];

        /** @var ReflectionMethod[][] $keyBindings */
        $inputHandlers = [];

        foreach ((new ReflectionClass($component))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach (Attributes::find(HandlesKey::class)->in($method)->all() as $handlesKey) {
                if ($handlesKey->key === null) {
                    $inputHandlers[] = $method;
                } else {
                    $keyBindings[$handlesKey->key->value][] = $method;
                }
            }
        }

        return [$keyBindings, $inputHandlers];
    }
}