<?php

declare(strict_types=1);

namespace Tempest\Console\Initializers;

use Tempest\Console\Commands\ScheduleTaskCommand;
use Tempest\Console\Components\InteractiveComponentRenderer;
use Tempest\Console\Console;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\GenericConsole;
use Tempest\Console\Highlight\TempestTerminalTheme;
use Tempest\Console\Highlight\TextTerminalTheme;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\StdinInputBuffer;
use Tempest\Console\Input\UnsupportedInputBuffer;
use Tempest\Console\Output\LogOutputBuffer;
use Tempest\Console\Output\StdoutOutputBuffer;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;

#[Singleton]
final class ConsoleInitializer implements Initializer
{
    public function initialize(Container $container): Console
    {
        $argumentBag = $container->get(ConsoleArgumentBag::class);

        if ($argumentBag->getCommandName() === ScheduleTaskCommand::NAME) {
            return $this->backgroundTaskConsole($container);
        }

        return $this->cliConsole($container);
    }

    public function backgroundTaskConsole(Container $container): GenericConsole
    {
        $textHighlighter = new Highlighter(new TextTerminalTheme());

        $container->singleton(ConsoleExceptionHandler::class, fn () => new ConsoleExceptionHandler(
            console: $container->get(Console::class),
            highlighter: $textHighlighter,
            argumentBag: $container->get(ConsoleArgumentBag::class),
        ));

        return new GenericConsole(
            output: $container->get(LogOutputBuffer::class),
            input: new UnsupportedInputBuffer(),
            highlighter: $textHighlighter,
        );
    }

    public function cliConsole(Container $container): GenericConsole
    {
        $terminalHighlighter = new Highlighter(new TempestTerminalTheme());

        $console = (new GenericConsole(
            output: $container->get(StdoutOutputBuffer::class),
            input: $container->get(StdinInputBuffer::class),
            highlighter: $terminalHighlighter,
        ))->setComponentRenderer($container->get(InteractiveComponentRenderer::class));

        $container->singleton(ConsoleExceptionHandler::class, fn () => new ConsoleExceptionHandler(
            console: $console,
            highlighter: $terminalHighlighter,
            argumentBag: $container->get(ConsoleArgumentBag::class),
        ));

        return $console;
    }
}
