<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\AppConfig;
use Tempest\Console\Console;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Exceptions\ConsoleException;
use Throwable;

final readonly class ConsoleExceptionMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private AppConfig $appConfig,
        private Console $console
    ) {
    }

    public function __invoke(ConsoleCommand $consoleCommand, ConsoleArgumentBag $argumentBag, callable $next): void
    {
        try {
            $next($consoleCommand, $argumentBag);
        } catch (ConsoleException $consoleException) {
            $consoleException->render($this->console);
        } catch (Throwable $throwable) {
            if (
                ! $this->appConfig->enableExceptionHandling
                || $this->appConfig->exceptionHandlers === []
            ) {
                throw $throwable;
            }

            foreach ($this->appConfig->exceptionHandlers as $exceptionHandler) {
                $exceptionHandler->handle($throwable);
            }
        }
    }
}
