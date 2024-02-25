<?php

declare(strict_types=1);

namespace Tempest\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleStyle;
use Throwable;

final readonly class ConsoleExceptionHandler implements ExceptionHandler
{
    public function __construct(private Console $console) {}

    public function handle(Throwable $throwable): void
    {
        $this->console->writeln(ConsoleStyle::BG_RED(' ' . $throwable::class . ' '));

        if ($message = $throwable->getMessage()) {
            $this->console->writeln(ConsoleStyle::BG_RED(' ' . $message . ' '));
        }

        $this->console->error($throwable->getFile() . ':' . $throwable->getLine());

        foreach ($throwable->getTrace() as $line) {
            $this->console->writeln(implode(' ', [
                '    -',
                ($line['class'] ?? '') . '::' . ($line['function'] ?? ''),
            ]));
        }
//        throw $throwable;
    }
}
