<?php

namespace Tempest\Bus;

interface Middleware
{
    public function __invoke(object $command, callable $next): void;
}
