<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\Exceptions\ConsoleException;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Core\Application\AppConfig;
use Throwable;

final readonly class ConsoleExceptionMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private AppConfig $appConfig,
        private Console $console
    ) {
    }

    public function __invoke(Invocation $invocation, callable $next): ExitCode
    {
        try {
            return $next($invocation);
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

        return ExitCode::ERROR;
    }
}
