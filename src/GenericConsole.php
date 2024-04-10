<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Support\Reflection\Attributes;

final class GenericConsole implements Console
{
    public function __construct(
        private readonly ConsoleInput $input,
        private readonly ConsoleOutput $output,
    ) {
    }

    public function delimiter(string $delimiter): ConsoleOutput
    {
        return $this->output->delimiter($delimiter);
    }

    public function readln(): string
    {
        return $this->input->readln();
    }

    public function ask(string $question, ?array $options = null, ?string $default = null): string
    {
        return $this->input->ask($question, $options, $default);
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->input->confirm($question, $default);
    }

    public function write(string $line, ConsoleOutputType $type = ConsoleOutputType::DEFAULT): ConsoleOutput
    {
        return $this->output->write($line, $type);
    }

    public function writeln(string $line = '', ConsoleOutputType $type = ConsoleOutputType::DEFAULT): ConsoleOutput
    {
        return $this->output->writeln($line, $type);
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

        foreach ((new ReflectionClass($component))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach (Attributes::find(HandlesKey::class)->in($method)->all() as $handlesKey) {
                $keyBindings[$handlesKey->key->value][] = $method;
            }
        }

        $this->switchToInteractiveMode();

        $this->clear()->write($component->render());

        while ($key = fread(STDIN, 16)) {
            $key = preg_replace(
                pattern: '/[^[:print:]\n]/u',
                replacement: '',
                subject: mb_convert_encoding($key, 'UTF-8', 'UTF-8'),
            );

            $handlersForKey = $keyBindings[$key] ?? [];

            foreach ($handlersForKey as $handler) {
                $return = $handler->invoke($component, $this);

                if ($return) {
                    $this->switchToNormalMode();

                    return $return;
                }
            }

            $this->clear()->write($component->render());
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
}
