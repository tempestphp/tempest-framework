<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Support;

use Tempest\Idempotency\Config\IdempotencyConfig;

final readonly class IdempotencyKeyResolver
{
    public function __construct(
        private IdempotencyConfig $config,
    ) {}

    public function recordKey(string $scope, string $key): string
    {
        $prefix = preg_replace('/[{}()\/\\@:]/', '_', $this->config->cachePrefix) ?? 'idempotency';

        return sprintf(
            '%s_%s',
            $prefix,
            hash('sha256', $scope . ':' . $key),
        );
    }

    public function lockKey(string $scope, string $key): string
    {
        return $this->recordKey($scope, $key) . '_lock';
    }
}
