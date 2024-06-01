<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Fiber;
use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\Console;
use Tempest\Console\Exceptions\InterruptException;
use Tempest\Console\HandlesKey;
use Tempest\Console\InteractiveComponent;
use Tempest\Console\Key;
use Tempest\Console\Terminal\Terminal;
use Tempest\Support\Reflection\Attributes;
use Tempest\Validation\Exceptions\InvalidValueException;
use Tempest\Validation\Rule;
use Tempest\Validation\Validator;

final class InteractiveComponentRenderer
{
    private array $validationErrors = [];
    private bool $shouldRerender = true;

    public function render(Console $console, InteractiveComponent $component, array $validation = []): mixed
    {
        $clone = clone $this;

        return $clone->renderComponent($console, $component, $validation);
    }

    private function renderComponent(Console $console, InteractiveComponent $component, array $validation = []): mixed
    {
        $terminal = $this->createTerminal($console);

        $fibers = [
            new Fiber(fn () => $this->applyKey($component, $console, $validation)),
            new Fiber(fn () => $this->renderFrames($component, $terminal)),
        ];

        try {
            while ($fibers !== []) {
                foreach ($fibers as $key => $fiber) {
                    if (! $fiber->isStarted()) {
                        $fiber->start();
                    }

                    $fiber->resume();

                    if ($fiber->isTerminated()) {
                        unset($fibers[$key]);

                        $return = $fiber->getReturn();

                        if ($return !== null) {
                            $this->closeTerminal($terminal);

                            return $return;
                        }
                    }
                }

                // If we're running within a fiber, we'll suspend here as well so that the parent can continue
                // This is needed for our testing helper
                if (Fiber::getCurrent()) {
                    Fiber::suspend();
                }
            }
        } catch (InterruptException $interruptException) {
            $this->closeTerminal($terminal);

            throw $interruptException;
        }

        $this->closeTerminal($terminal);

        return null;
    }

    private function applyKey(InteractiveComponent $component, Console $console, array $validation): mixed
    {
        [$keyBindings, $inputHandlers] = $this->resolveHandlers($component);

        while (true) {
            usleep(5000);
            $key = $console->read(16);

            // If there's no keypress, continue
            if ($key === '') {
                Fiber::suspend();

                continue;
            }

            // If ctrl+c or ctrl+d, we'll exit
            if ($key === Key::CTRL_C->value || $key === Key::CTRL_D->value) {
                throw new InterruptException();
            }

            $this->shouldRerender = true;

            $return = null;

            if ($handlersForKey = $keyBindings[$key] ?? null) {
                // Apply specific key handlers
                foreach ($handlersForKey as $handler) {
                    $return ??= $handler->invoke($component);
                }
            } else {
                // Apply catch-all key handlers
                foreach ($inputHandlers as $handler) {
                    $return ??= $handler->invoke($component, $key);
                }
            }

            // If nothing's returned, we can continue waiting for the next key press
            if ($return === null) {
                Fiber::suspend();

                continue;
            }

            // If something's returned, we'll need to validate the result
            $this->validationErrors = [];

            $failingRule = $this->validate($return, $validation);

            // If invalid, we'll remember the validation message and continue
            if ($failingRule) {
                $this->validationErrors[] = '<error>' . $failingRule->message() . '</error>';
                Fiber::suspend();

                continue;
            }

            // If valid, we can return
            return $return;
        }
    }

    private function renderFrames(InteractiveComponent $component, Terminal $terminal): mixed
    {
        while (true) {
            usleep(5000);

            // If there are no updates,
            // we won't spend time re-rendering the same frame
            if (! $this->shouldRerender) {
                Fiber::suspend();

                continue;
            }

            // Rerender the frames, it could be one or more
            $frames = $terminal->render(
                component: $component,
                footerLines: $this->validationErrors,
            );

            // Looping over the frames will display them
            // (this happens within the Terminal class, might need to refactor)
            // We suspend between each frame to allow key press interruptions
            foreach ($frames as $frame) {
                Fiber::suspend();
            }

            $return = $frames->getReturn();

            // Everything's rerendered
            $this->shouldRerender = false;

            if ($return !== null) {
                return $return;
            }
        }
    }

    private function resolveHandlers(InteractiveComponent $component): array
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

    /**
     * @param mixed $value
     * @param \Tempest\Validation\Rule[] $validation
     * @return Rule|null
     */
    private function validate(mixed $value, array $validation): ?Rule
    {
        $validator = new Validator();

        try {
            $validator->validateValue($value, $validation);
        } catch (InvalidValueException $e) {
            return $e->failingRules[0];
        }

        return null;
    }

    private function createTerminal(Console $console): Terminal
    {
        $terminal = new Terminal($console);
        $terminal->cursor->clearAfter();
        stream_set_blocking(STDIN, false);

        return $terminal;
    }

    private function closeTerminal(Terminal $terminal): void
    {
        $terminal->switchToNormalMode();
        stream_set_blocking(STDIN, true);
    }
}
