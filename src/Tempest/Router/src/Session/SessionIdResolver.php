<?php

declare(strict_types=1);

namespace Tempest\Router\Session;

interface SessionIdResolver
{
    public function resolve(): SessionId;
}
