<?php

declare(strict_types=1);

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
    public function __construct(private Console $console)
    {
    }

    public function __invoke(ConsoleComponent $component): mixed
    {
        $terminal = new InteractiveTerminal($this->console);

        [$keyBindings, $inputHandlers] = $this->resolveHandlers($component);

        $terminal->cursor->clearAfter();

        $return = $terminal->render($component);

        if ($return !== null) {
            return $return;
        }

        while ($key = $this->console->read(16)) {
            $return = null;

            if ($handlersForKey = $keyBindings[$key] ?? null) {
                // Specific key handlers
                foreach ($handlersForKey as $handler) {
                    $return ??= $handler->invoke($component);
                }
            } else {
                // Catch-all key handlers
                foreach ($inputHandlers as $handler) {
                    $return ??= $handler->invoke($component, $key);
                }
            }

            // If a handler returned a result, we'll return
            if ($return !== null) {
                $terminal->switchToNormalMode();

                return $return;
            }

            // Rerender the component
            $terminal->render($component);
        }

        return null;
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
