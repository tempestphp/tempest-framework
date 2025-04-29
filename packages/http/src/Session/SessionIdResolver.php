<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

interface SessionIdResolver
{
    public function resolve(): SessionId;
}
