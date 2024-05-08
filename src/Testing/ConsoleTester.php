<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use PHPUnit\Framework\Assert;
use Tempest\AppConfig;
use Tempest\Console\Components\UnsupportedComponentRenderer;
use Tempest\Console\Console;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\GenericConsole;
use Tempest\Console\Input\UnsupportedInputBuffer;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Console\Output\OutputBuffer;
use Tempest\Container\Container;
use Tempest\Highlight\Highlighter;

final class ConsoleTester
{
    private ?MemoryOutputBuffer $output = null;

    public function __construct(private Container $container)
    {
    }

    public function call(string $command): self
    {
        $clone = clone $this;

        $appConfig = $this->container->get(AppConfig::class);

        $clone->container->singleton(OutputBuffer::class, fn () => new MemoryOutputBuffer());

        $clone->container->singleton(
            Console::class,
            fn () => new GenericConsole(
                output: $clone->container->get(OutputBuffer::class),
                input: new UnsupportedInputBuffer(),
                componentRenderer: new UnsupportedComponentRenderer(),
                highlighter: $clone->container->get(Highlighter::class)
            ),
        );

        $appConfig->exceptionHandlers[] = $clone->container->get(ConsoleExceptionHandler::class);

        $argumentBag = new ConsoleArgumentBag(['tempest', ...explode(' ', $command)]);
        $clone->container->singleton(ConsoleArgumentBag::class, $argumentBag);

        $application = new ConsoleApplication(
            container: $clone->container,
            argumentBag: $clone->container->get(ConsoleArgumentBag::class),
        );

        $application->run();

        $clone->output = $clone->container->get(OutputBuffer::class);

        return $clone;
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
