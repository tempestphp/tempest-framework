<?php

declare(strict_types=1);

namespace Tempest\Idempotency;

use Tempest\Http\Request;

interface IdempotencyScopeResolver
{
    public function resolve(Request $request): string;
}
