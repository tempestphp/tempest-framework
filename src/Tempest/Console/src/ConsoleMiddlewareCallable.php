<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;
use Tempest\Console\Initializers\Invocation;

final readonly class ConsoleMiddlewareCallable
{
    public function __construct(
        private Closure $closure,
    ) {
    }

    public function __invoke(Invocation $invocation): ExitCode
    {
        return ($this->closure)($invocation);
    }
}
