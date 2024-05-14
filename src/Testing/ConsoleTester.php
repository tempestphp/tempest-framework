<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use Closure;
use Exception;
use Fiber;
use PHPUnit\Framework\Assert;
use ReflectionMethod;
use Tempest\AppConfig;
use Tempest\Console\Components\InteractiveComponentRenderer;
use Tempest\Console\Console;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\GenericConsole;
use Tempest\Console\Input\InputBuffer;
use Tempest\Console\Input\MemoryInputBuffer;
use Tempest\Console\Key;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Console\Output\OutputBuffer;
use Tempest\Container\Container;
use Tempest\Highlight\Highlighter;
use Tempest\Support\Reflection\Attributes;

final class ConsoleTester
{
    private ?MemoryOutputBuffer $output = null;
    private ?MemoryInputBuffer $input = null;
    private ?InteractiveComponentRenderer $componentRenderer = null;

    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function call(string|Closure|array $command): self
    {
        $clone = clone $this;

        $clone->container->singleton(OutputBuffer::class, new MemoryOutputBuffer());
        $clone->container->singleton(InputBuffer::class, new MemoryInputBuffer());

        $console = new GenericConsole(
            output: $clone->container->get(OutputBuffer::class),
            input: $clone->container->get(InputBuffer::class),
            highlighter: $clone->container->get(Highlighter::class),
        );

        if ($this->componentRenderer) {
            $console->setComponentRenderer($this->componentRenderer);
        }

        $clone->container->singleton(Console::class, $console);

        $appConfig = $this->container->get(AppConfig::class);
        $appConfig->exceptionHandlers[] = $clone->container->get(ConsoleExceptionHandler::class);

        $clone->output = $clone->container->get(OutputBuffer::class);
        $clone->input = $clone->container->get(InputBuffer::class);

        if ($command instanceof Closure) {
            $fiber = new Fiber(function () use ($command, $console) {
                $command($console);
            });
        } else {
            if (is_string($command) && class_exists($command)) {
                $command = [$command, '__invoke'];
            }

            if (is_array($command) || class_exists($command)) {
                $attribute = Attributes::find(ConsoleCommand::class)
                    ->in(new ReflectionMethod(...$command))
                    ->first();

                if (! $attribute) {
                    throw new Exception("Could not resolve console command from {$command[0]}::{$command[1]}");
                }

                $command = $attribute->getName();
            }

            $fiber = new Fiber(function () use ($command, $clone) {
                $clone->container->singleton(ConsoleArgumentBag::class, new ConsoleArgumentBag(['tempest', ...explode(' ', $command)]));

                $application = new ConsoleApplication(
                    container: $clone->container,
                    argumentBag: $clone->container->get(ConsoleArgumentBag::class),
                );

                $application->run();
            });
        }

        $fiber->start();

        if ($clone->componentRenderer) {
            $clone->input("\e[1;1R"); // Set cursor for interactive testing
        }

        return $clone;
    }

    public function input(int|string|Key $input): self
    {
        $this->output->clear();

        $this->input->add($input);

        return $this;
    }

    public function submit(int|string $input = ''): self
    {
        $input = (string)$input;

        $this->input($input . Key::ENTER->value);

        return $this;
    }

    public function print(): self
    {
        echo "OUTPUT:\n";
        echo $this->output->asUnformattedString();

        return $this;
    }

    public function printFormatted(): self
    {
        echo $this->output->asFormattedString();

        return $this;
    }

    public function useInteractiveTerminal(): self
    {
        $this->componentRenderer = new InteractiveComponentRenderer();

        return $this;
    }

    public function assertContains(string $text): self
    {
        Assert::assertStringContainsString(
            $text,
            $this->output->asUnformattedString(),
            sprintf(
                'Failed to assert that console output included text: %s. These lines were printed: %s',
                $text,
                PHP_EOL . PHP_EOL . $this->output->asUnformattedString() . PHP_EOL,
            ),
        );

        return $this;
    }

    public function assertDoesNotContain(string $text): self
    {
        Assert::assertStringNotContainsString(
            $text,
            $this->output->asUnformattedString(),
            sprintf(
                'Failed to assert that console output did not include text: %s. These lines were printed: %s',
                $text,
                PHP_EOL . PHP_EOL . $this->output->asUnformattedString() . PHP_EOL,
            ),
        );

        return $this;
    }

    public function assertContainsFormattedText(string $text): self
    {
        Assert::assertStringContainsString(
            $text,
            $this->output->asFormattedString(),
            sprintf(
                'Failed to assert that console output included formatted text: %s. These lines were printed: %s',
                $text,
                PHP_EOL . $this->output->asFormattedString(),
            ),
        );

        return $this;
    }
}
