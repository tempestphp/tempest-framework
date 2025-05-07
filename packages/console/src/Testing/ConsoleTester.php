<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use Closure;
use Fiber;
use PHPUnit\Framework\Assert;
use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Components\InteractiveComponentRenderer;
use Tempest\Console\Console;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\ExitCode;
use Tempest\Console\GenericConsole;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\MemoryInputBuffer;
use Tempest\Console\InputBuffer;
use Tempest\Console\Key;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Console\OutputBuffer;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Highlight\Highlighter;

final class ConsoleTester
{
    private (OutputBuffer&MemoryOutputBuffer)|null $output = null;

    private (InputBuffer&MemoryInputBuffer)|null $input = null;

    private ?InteractiveComponentRenderer $componentRenderer = null;

    private ?ExitCode $exitCode = null;

    private bool $withPrompting = true;

    private (Console&GenericConsole)|null $console = null;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function call(string|Closure|array $command, string|array $arguments = []): self
    {
        $clone = clone $this;

        $this->output ??= new MemoryOutputBuffer();
        $this->output->clear();
        $memoryOutputBuffer = $this->output;
        $clone->container->singleton(OutputBuffer::class, $memoryOutputBuffer);

        $this->input ??= new MemoryInputBuffer();
        $this->input->clear();
        $memoryInputBuffer = $this->input;
        $clone->container->singleton(InputBuffer::class, $memoryInputBuffer);

        $this->console ??= new GenericConsole(
            output: $memoryOutputBuffer,
            input: $memoryInputBuffer,
            highlighter: $clone->container->get(Highlighter::class, 'console'),
            executeConsoleCommand: $clone->container->get(ExecuteConsoleCommand::class),
            argumentBag: $clone->container->get(ConsoleArgumentBag::class),
        );

        $console = $this->console;

        if ($this->withPrompting === false) {
            $console->disablePrompting();
        }

        if ($this->componentRenderer !== null) {
            $console->setComponentRenderer($this->componentRenderer);
        }

        $clone->container->singleton(Console::class, $console);

        $appConfig = $this->container->get(AppConfig::class);
        $appConfig->exceptionProcessors[] = $clone->container->get(ConsoleExceptionHandler::class);

        $clone->output = $memoryOutputBuffer;
        $clone->input = $memoryInputBuffer;

        if ($command instanceof Closure) {
            $fiber = new Fiber(function () use ($clone, $command, $console): void {
                $clone->exitCode = $command($console) ?? ExitCode::SUCCESS;
            });
        } else {
            $fiber = new Fiber(function () use ($command, $arguments, $clone): void {
                $clone->container->singleton(ConsoleArgumentBag::class, new ConsoleArgumentBag(['tempest']));
                $clone->exitCode = $this->container->invoke(
                    ExecuteConsoleCommand::class,
                    command: $command,
                    arguments: $arguments,
                );
            });
        }

        $fiber->start();

        if ($clone->componentRenderer !== null) {
            $clone->input("\e[1;1R"); // Set cursor for interactive testing
        }

        return $clone;
    }

    public function complete(?string $command = null): self
    {
        if ($command) {
            $input = explode(' ', $command);

            $inputString = implode(' ', array_map(
                fn (string $item) => "--input=\"{$item}\"",
                $input,
            ));
        } else {
            $inputString = '';
        }

        return $this->call("_complete --current=0 --input=\"./tempest\" {$inputString}");
    }

    public function input(int|string|Key $input): self
    {
        $this->output->clear();

        $this->input->add($input);

        return $this;
    }

    public function submit(int|string $input = ''): self
    {
        $input = (string) $input;

        $this->input($input . Key::ENTER->value);

        return $this;
    }

    public function confirm(): self
    {
        return $this->submit('yes');
    }

    public function deny(): self
    {
        return $this->submit('no');
    }

    public function print(): self
    {
        echo 'OUTPUT:' . PHP_EOL;
        echo $this->output->asUnformattedString();

        return $this;
    }

    public function printFormatted(): self
    {
        echo $this->output->asFormattedString();

        return $this;
    }

    public function getBuffer(?callable $callback = null): array
    {
        $buffer = array_map('trim', $this->output->getBufferWithoutFormatting());

        $this->output->clear();

        if ($callback !== null) {
            return $callback($buffer);
        }

        return $buffer;
    }

    public function useInteractiveTerminal(): self
    {
        $this->componentRenderer = new InteractiveComponentRenderer();

        return $this;
    }

    public function assertSee(string $text): self
    {
        return $this->assertContains($text);
    }

    public function assertSeeCount(string $text, int $expectedCount): self
    {
        $actualCount = substr_count($this->output->asUnformattedString(), $text);

        Assert::assertSame(
            $expectedCount,
            $actualCount,
            sprintf(
                'Failed to assert that console output counted: %s exactly %d times. These lines were printed: %s',
                $text,
                $expectedCount,
                PHP_EOL . PHP_EOL . $this->output->asUnformattedString() . PHP_EOL,
            ),
        );

        return $this;
    }

    public function assertNotSee(string $text): self
    {
        return $this->assertDoesNotContain($text);
    }

    public function assertContains(string $text, bool $ignoreLineEndings = true): self
    {
        $method = $ignoreLineEndings ? 'assertStringContainsStringIgnoringLineEndings' : 'assertStringContainsString';

        Assert::$method(
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

    public function assertJson(): self
    {
        Assert::assertJson($this->output->asUnformattedString());

        return $this;
    }

    public function assertExitCode(ExitCode $exitCode): self
    {
        Assert::assertNotNull($this->exitCode, "Expected {$exitCode->name}, but instead no exit code was set — maybe you missed providing some input?");

        Assert::assertSame($exitCode, $this->exitCode, "Expected the exit code to be {$exitCode->name}, instead got {$this->exitCode->name}");

        return $this;
    }

    public function assertSuccess(): self
    {
        $this->assertExitCode(ExitCode::SUCCESS);

        return $this;
    }

    public function assertError(): self
    {
        $this->assertExitCode(ExitCode::ERROR);

        return $this;
    }

    public function assertCancelled(): self
    {
        $this->assertExitCode(ExitCode::CANCELLED);

        return $this;
    }

    public function assertInvalid(): self
    {
        $this->assertExitCode(ExitCode::INVALID);

        return $this;
    }

    public function withoutPrompting(): self
    {
        $this->withPrompting = false;

        return $this;
    }

    public function withPrompting(): self
    {
        $this->withPrompting = true;

        return $this;
    }

    public function dd(): self
    {
        ld($this->output->asUnformattedString());

        return $this;
    }
}
