<?php

declare(strict_types=1);

namespace Tempest\Commands;

interface Middleware
{
    public function __invoke(object $command, callable $next): void;
}
