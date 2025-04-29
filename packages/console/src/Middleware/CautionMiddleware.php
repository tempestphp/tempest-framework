<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Core\AppConfig;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class CautionMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
        private AppConfig $appConfig,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        $environment = $this->appConfig->environment;

        if ($environment->isProduction() || $environment->isStaging()) {
            $continue = $this->console->confirm('Caution! Do you wish to continue?');

            if (! $continue) {
                return ExitCode::CANCELLED;
            }
        }

        return $next($invocation);
    }
}
