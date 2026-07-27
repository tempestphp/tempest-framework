<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Fiber;
use Tempest\Console\Console;
use Tempest\Console\Exceptions\InterruptException;
use Tempest\Console\HandlesKey;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Key;
use Tempest\Console\Terminal\Terminal;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\MethodReflector;
use Tempest\Support\Arr;
use Tempest\Validation\Validator;

use function Tempest\Support\arr;

final class InteractiveComponentRenderer
{
    private array $afterRenderCallbacks = [];

    private array $validationErrors = [];

    private bool $shouldRerender = true;

    /** @var list<string> */
    private array $pendingKeys = [];

    private string $pendingInput = '';

    public function __construct(
        private readonly Validator $validator,
    ) {}

    public function render(Console $console, InteractiveConsoleComponent $component, array $validation = []): mixed
    {
        $clone = clone $this;

        return $clone->renderComponent($console, $component, $validation);
    }

    private function renderComponent(Console $console, InteractiveConsoleComponent $component, array $validation = []): mixed
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

                        if (! is_null($return = $fiber->getReturn())) {
                            return $return;
                        }
                    }
                }

                // If we're running within a fiber, we'll suspend here as well so that the parent can continue
                // This is needed for our testing helper
                if (Fiber::getCurrent() instanceof Fiber) {
                    Fiber::suspend();
                }
            }
        } finally {
            $this->closeTerminal($terminal);
        }

        return null;
    }

    private function applyKey(InteractiveConsoleComponent $component, Console $console, array $validation): mixed
    {
        [$keyBindings, $inputHandlers] = $this->resolveHandlers($component);

        while (true) {
            while ($callback = array_shift($this->afterRenderCallbacks)) {
                $callback($component);
            }

            usleep(50);
            $key = $this->readKey($console);

            // If there's no keypress, continue.
            if ($key === '') {
                Fiber::suspend();

                continue;
            }

            // Otherwise, we will re-render after processing the key.
            $this->shouldRerender = true;

            if ($component->getState() === ComponentState::BLOCKED) {
                continue;
            }

            /** @var MethodReflector[] $handlersForKey */
            $handlersForKey = $keyBindings[$key] ?? [];

            // If we have multiple handlers, we put the ones that return nothing
            // first because the ones that return something will be overridden otherwise.
            usort($handlersForKey, fn (MethodReflector $a, MethodReflector $b) => $b->getReturnType()->equals('void') <=> $a->getReturnType()->equals('void'));

            // CTRL+C and CTRL+D means we exit the CLI, but only if there is no custom
            // handler. When we exit, we want one last render to display pretty
            // styles, so we will throw the exception in the next loop.
            if ($handlersForKey === [] && ($key === Key::CTRL_C->value || $key === Key::CTRL_D->value)) {
                $component->setState(ComponentState::CANCELLED);
                $this->afterRenderCallbacks[] = fn () => throw new InterruptException();
                $this->shouldRerender = true;
                Fiber::suspend();

                continue;
            }

            $return = null;

            // If we have handlers for that key, apply them.
            foreach ($handlersForKey as $handler) {
                $return ??= $handler->invokeArgs($component);
            }

            // If we didn't have any handler for the key,
            // we call catch-all handlers.
            if ($handlersForKey === []) {
                foreach ($inputHandlers as $handler) {
                    $return ??= $handler->invokeArgs($component, [$key]);
                }
            }

            // If nothing's returned, we can continue waiting for the next key press
            if ($return === null) {
                Fiber::suspend();

                continue;
            }

            // If something's returned, we'll need to validate the result
            $this->validationErrors = [];

            $validationError = $this->validate($return, $validation);

            // If invalid, we'll remember the validation message and continue
            if ($validationError !== null) {
                $component->setState(ComponentState::ERROR);
                $this->validationErrors[] = $validationError;
                Fiber::suspend();

                continue;
            }

            // The component is done, we can re-render and return.
            $component->setState(ComponentState::DONE);
            Fiber::suspend();

            // If valid, we can return
            return $return;
        }
    }

    private function readKey(Console $console): string
    {
        while ($this->pendingKeys === []) {
            $input = $console->read(16);

            if ($input === '') {
                return '';
            }

            $this->pendingInput .= $input;
            $this->pendingKeys = $this->splitKeys($this->pendingInput);
        }

        return array_shift($this->pendingKeys);
    }

    /** @return list<string> */
    private function splitKeys(string &$input): array
    {
        /** @var null|list<string> $knownKeys */
        static $knownKeys = null;

        if ($knownKeys === null) {
            $knownKeys = array_map(
                static fn (Key $key): string => $key->value,
                Key::cases(),
            );
            usort($knownKeys, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));
        }

        $keys = [];

        while ($input !== '') {
            foreach ($knownKeys as $knownKey) {
                if (! str_starts_with($input, $knownKey)) {
                    continue;
                }

                $keys[] = $knownKey;
                $input = substr($input, strlen($knownKey));

                continue 2;
            }

            if (str_starts_with($input, "\e")) {
                $matches = [];
                preg_match('/^\e(?:\[[0-?]*[ -\/]*[@-~]|O.)/s', $input, $matches);
                $key = $matches[0] ?? mb_substr($input, 0, 2);
            } else {
                $length = $this->getUtf8CharacterLength($input);

                if (strlen($input) < $length) {
                    break;
                }

                $key = substr($input, 0, $length);
            }

            $keys[] = $key;
            $input = substr($input, strlen($key));
        }

        return $keys;
    }

    private function getUtf8CharacterLength(string $input): int
    {
        $firstByte = ord($input[0]);

        return match (true) {
            ($firstByte & 0x80) === 0 => 1,
            ($firstByte & 0xE0) === 0xC0 => 2,
            ($firstByte & 0xF0) === 0xE0 => 3,
            ($firstByte & 0xF8) === 0xF0 => 4,
            default => 1,
        };
    }

    private function renderFrames(InteractiveConsoleComponent $component, Terminal $terminal): mixed
    {
        while (true) {
            usleep(100);

            // If there are no updates,
            // we won't spend time re-rendering the same frame
            if (! $this->shouldRerender) {
                Fiber::suspend();

                continue;
            }

            $this->shouldRerender = false;

            // Rerender the frames, it could be one or more
            $frames = $terminal->render(
                component: $component,
                validationErrors: $this->validationErrors,
            );

            // Looping over the frames will display them
            // (this happens within the Terminal class, might need to refactor)
            // We suspend between each frame to allow key press interruptions
            foreach ($frames as $frame) {
                Fiber::suspend();
            }

            $return = $frames->getReturn();

            if ($return !== null) {
                return $return;
            }
        }
    }

    private function resolveHandlers(InteractiveConsoleComponent $component): array
    {
        /** @var \Tempest\Reflection\MethodReflector[][] $keyBindings */
        $keyBindings = [];

        $inputHandlers = [];

        foreach (new ClassReflector($component)->getPublicMethods() as $method) {
            foreach ($method->getAttributes(HandlesKey::class) as $handlesKey) {
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
     * @param \Tempest\Validation\Rule[] $validation
     */
    private function validate(mixed $value, array $validation): ?string
    {
        $failingRules = $this->validator->validateValue($value, $validation);

        if ($failingRules === []) {
            return null;
        }

        return $this->validator->getErrorMessage(
            rule: Arr\first($failingRules),
        );
    }

    public function isComponentSupported(Console $console, InteractiveConsoleComponent $component): bool
    {
        if (! arr($component->extensions ?? [])->every(fn (string $ext) => extension_loaded($ext))) {
            return false;
        }

        return Terminal::supportsTty();
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
        $terminal->placeCursorToEnd();
        $terminal->switchToNormalMode();
        stream_set_blocking(STDIN, true);
    }
}
