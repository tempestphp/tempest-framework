<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Tests\Fixtures;

use Tempest\Http\Request;
use Tempest\Idempotency\IdempotencyScopeResolver;

final readonly class FixedScopeResolver implements IdempotencyScopeResolver
{
    public function __construct(
        private string $scope = 'test-user',
    ) {}

    public function resolve(Request $request): string
    {
        return $this->scope;
    }
}
