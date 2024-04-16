<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\ConsoleComponent;
use Tempest\Console\ConsoleComponentRenderer;
use Tempest\Console\HandlesKey;
use Tempest\Console\Key;
use Tempest\Support\Reflection\Attributes;

final class TestConsoleComponentRenderer implements ConsoleComponentRenderer
{
    private ?ConsoleComponent $component = null;

    /** @var ReflectionMethod[] */
    private array $keybindings = [];

    /** @var ReflectionMethod[] */
    private array $inputHandlers = [];

    public function renderCurrentComponent(): mixed
    {
        return $this->render($this->component);
    }

    public function render(ConsoleComponent $component): mixed
    {
        $this->component = $component;

        [$keyBindings, $inputHandlers] = $this->resolveHandlers($component);

        $this->keybindings = $keyBindings;
        $this->inputHandlers = $inputHandlers;

        return $this->component->render();
    }

    public function write(string $text): self
    {
        $characters = str_split($text);

        foreach ($characters as $character) {
            foreach ($this->inputHandlers as $handler) {
                $handler->invoke($this->component, $character);
            }
        }

        return $this;
    }

    public function up(): self
    {
        $this->handleKey(Key::UP);

        return $this;
    }

    public function down(): self
    {
        $this->handleKey(Key::DOWN);

        return $this;
    }

    public function left(): self
    {
        $this->handleKey(Key::LEFT);

        return $this;
    }

    public function right(): self
    {
        $this->handleKey(Key::RIGHT);

        return $this;
    }

    public function enter(): self
    {
        $this->handleKey(Key::ENTER);

        return $this;
    }

    public function backspace(): self
    {
        $this->handleKey(Key::BACKSPACE);

        return $this;
    }

    public function delete(): self
    {
        $this->handleKey(Key::DELETE);

        return $this;
    }

    public function space(): self
    {
        $this->handleKey(Key::SPACE);

        return $this;
    }

    private function handleKey(Key $key): void
    {
        $handlersForKey = $this->keybindings[$key->value] ?? [];

        /** @phpstan-ignore-next-line */
        foreach ($handlersForKey as $handler) {
            $handler->invoke($this->component, $key->value);
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
