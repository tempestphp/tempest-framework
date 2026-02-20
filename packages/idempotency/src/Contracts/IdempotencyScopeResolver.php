<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Contracts;

use Tempest\Http\Request;

interface IdempotencyScopeResolver
{
    public function resolve(Request $request): string;
}
