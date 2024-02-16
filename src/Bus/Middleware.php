<?php

declare(strict_types=1);

namespace Tempest\Bus;

interface Middleware
{
    public function __invoke(object $command, callable $next): mixed;
}
