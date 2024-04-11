<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\Components\QuestionComponent;
use Tempest\Console\Components\TextBoxComponent;
use Tempest\Support\Reflection\Attributes;

final class GenericConsole implements Console
{
    public function __construct(
        private readonly ConsoleInput $input,
        private readonly ConsoleOutput $output,
    ) {}

    public function delimiter(string $delimiter): ConsoleOutput
    {
        return $this->output->delimiter($delimiter);
    }

    public function readln(): string
    {
        return $this->input->readln();
    }

    public function ask(string $question, ?array $options = null): string
    {
        if ($options === null) {
            return $this->component(new TextBoxComponent($question));
        }

        return $this->component(new QuestionComponent($question, $options));
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->input->confirm($question, $default);
    }

    public function write(string $line, ConsoleOutputType $type = ConsoleOutputType::DEFAULT): self
    {
        $this->output->write($line, $type);

        return $this;
    }

    public function writeln(string $line = '', ConsoleOutputType $type = ConsoleOutputType::DEFAULT): self
    {
        $this->output->writeln($line, $type);

        return $this;
    }

    public function info(string $line): ConsoleOutput
    {
        return $this->output->info($line);
    }

    public function error(string $line): ConsoleOutput
    {
        return $this->output->error($line);
    }

    public function success(string $line): ConsoleOutput
    {
        return $this->output->success($line);
    }

    public function when(mixed $expression, callable $callback): ConsoleOutput
    {
        return $this->output->when($expression, $callback);
    }

    public function component(ConsoleComponent $component): mixed
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

        $this->switchToInteractiveMode();

        $this
            ->clear()
            ->write($component->render())
            ->placeCursor($component);

        while ($key = fread(STDIN, 16)) {
            $return = null;

            if ($handlersForKey = $keyBindings[$key] ?? null) {
                foreach ($handlersForKey as $handler) {
                    $return ??= $handler->invoke($component, $this);
                }
            } else {
                foreach ($inputHandlers as $handler) {
                    $return ??= $handler->invoke($component, $key, $this);
                }
            }

            if ($return) {
                $this->switchToNormalMode()->writeln();

                return $return;
            }

            $this
                ->clear()
                ->write($component->render())
                ->placeCursor($component);
        }
    }

    private function clear(): self
    {
        system("clear");

        return $this;
    }

    private function switchToInteractiveMode(): self
    {
        system("stty -echo");
        system("stty -icanon");

        return $this;
    }

    private function switchToNormalMode(): self
    {
        system("stty echo");
        system("stty icanon");

        return $this;
    }

    private function placeCursor(ConsoleComponent $component): self
    {
        if (! $component instanceof HasCursor) {
            return $this;
        }

        // Move cursor to 0,0
        $this->write("\e[f");

        $position = $component->getCursorPosition();

        for ($x = 0; $x < $position->x; $x++) {
            // Move right
            $this->write("\e[C");
        }

        for ($y = 0; $y < $position->y; $y++) {
            // Move down
            $this->write("\e[B");
        }

        return $this;
    }
}
