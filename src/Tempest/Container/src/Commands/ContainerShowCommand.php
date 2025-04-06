<?php

namespace Tempest\Container\Commands;

use Closure;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\FunctionReflector;

use function Tempest\Support\Arr\sort;
use function Tempest\Support\Arr\sort_keys;
use function Tempest\Support\str;
use function Tempest\Support\Str\after_last;
use function Tempest\Support\Str\before_last;
use function Tempest\Support\Str\contains;

final class ContainerShowCommand
{
    public function __construct(
        private readonly Container $container,
        private readonly Console $console,
    ) {}

    #[ConsoleCommand(description: 'Shows the container bindings')]
    public function __invoke(): ExitCode
    {
        if (! ($this->container instanceof GenericContainer)) {
            $this->console->error('The registered container instance does not expose its bindings.');

            return ExitCode::ERROR;
        }

        $this->listBindings(
            title: 'Initializers',
            bindings: sort($this->container->getInitializers()),
            formatKey: fn (string $class): string => $this->formatClassKey($class),
            formatValue: fn (string $_, string $initializer): string => $this->formatClassValue($initializer),
            reject: static fn (string $_, string $initializer): bool => contains($initializer, ['\\Stubs', '\\Fixtures']),
        );

        $this->listBindings(
            title: 'Dyanmic initializers',
            bindings: sort($this->container->getDynamicInitializers()),
            formatKey: fn (int $_, string $class): string => $this->formatClassKey($class),
            formatValue: static function (int $_, string $class): string {
                $name = new ClassReflector($class)
                    ->getMethod('initialize')
                    ->getReturnType()
                    ->getName();

                return match ($name) {
                    'object' => "<style='fg-gray'>object</style>",
                    default => "<style='fg-blue'>{$name}</style>",
                };
            },
        );

        $this->listBindings('Definitions', sort_keys($this->container->getDefinitions()));
        $this->listBindings('Singletons', sort_keys($this->container->getSingletons()));

        return ExitCode::SUCCESS;
    }

    private function listBindings(string $title, array $bindings, ?Closure $formatKey = null, ?Closure $formatValue = null, ?Closure $reject = null): void
    {
        if (! $bindings) {
            return;
        }

        $reject ??= static fn (): bool => false;
        $formatKey ??= fn (int|string $key): string => $this->formatClassKey($key);
        $formatValue ??= fn (int|string $key, mixed $value): string => $this->formatClassValue($value, $key);

        $this->console->header($title);

        foreach ($bindings as $class => $definition) {
            if ($reject($class, $definition)) {
                continue;
            }

            $this->console->keyValue(
                key: $formatKey($class, $definition),
                value: $formatValue($class, $definition),
            );
        }
    }

    private function formatClassValue(string|object $class, mixed $key = null): string
    {
        if ($class instanceof Closure) {
            $serialized = str(new FunctionReflector($class)->getName())->afterFirst(':')->stripEnd('}');
            $declaringClass = $serialized->before('::');
            $method = $serialized->between('::', '():');
            $line = $serialized->afterLast(':');

            return sprintf(
                "<style='fg-blue dim'>%s</style><style='dim'>::</style><style='fg-blue'>%s</style><style='dim'>():</style><style='fg-blue'>%s</style>",
                $declaringClass,
                $method,
                $line,
            );
        }

        if (! is_string($class)) {
            $class = $class::class;
        }

        if ($key === $class) {
            return "<style='fg-green dim bold'>SELF</style>";
        }

        $namespace = before_last($class, '\\');
        $name = after_last($class, '\\');

        return sprintf("<style='fg-blue dim'>%s\\</style><style='fg-blue'>%s</style>", $namespace, $name);
    }

    private function formatClassKey(string $class): string
    {
        $namespace = before_last($class, '\\');
        $name = after_last($class, '\\');

        return sprintf("<style='fg-gray'>%s\\</style>%s", $namespace, $name);
    }
}
