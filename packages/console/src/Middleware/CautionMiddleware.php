<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Core\Environment;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class CautionMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
        private Environment $environment,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        if ($this->environment->requiresCaution()) {
            if ($this->console->isForced) {
                return $next($invocation);
            }

            $continue = $this->console->confirm('This command might be destructive. Do you wish to continue?');

            if (! $continue) {
                return ExitCode::CANCELLED;
            }
        }

        return $next($invocation);
    }
}
