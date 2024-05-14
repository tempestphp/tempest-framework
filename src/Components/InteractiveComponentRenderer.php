<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

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

    public function render(
        Console $console,
        InteractiveComponent $component,
        array $validation = []
    ): mixed {
        $terminal = new Terminal($console);

        [$keyBindings, $inputHandlers] = $this->resolveHandlers($component);

        $terminal->cursor->clearAfter();

        $return = $terminal->render($component);

        if ($return !== null) {
            $terminal->switchToNormalMode();

            return $return;
        }

        while ($key = $console->read(16)) {
            if ($key === Key::CTRL_C->value || $key === Key::CTRL_D->value) {
                $terminal->switchToNormalMode();

                throw new InterruptException();
            }

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

            if ($return === null) {
                $terminal->render($component, footerLines: $this->validationErrors);
            } else {
                $this->validationErrors = [];

                $failingRule = $this->validate($return, $validation);

                if ($failingRule) {
                    $this->validationErrors[] = '<error>' . $failingRule->message() . '</error>';
                }

                if ($this->validationErrors) {
                    $terminal->render($component, footerLines: $this->validationErrors);
                } else {
                    $terminal->render($component, renderFooter: false);
                    $terminal->switchToNormalMode();

                    return $return;
                }
            }
        }

        return null;
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
}
