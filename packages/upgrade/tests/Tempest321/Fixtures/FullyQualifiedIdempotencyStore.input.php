<?php

final class RedisIdempotencyStore implements \Tempest\Idempotency\Store\IdempotencyStore
{
    public function find(string $scope, string $key): ?\Tempest\Idempotency\Store\IdempotencyRecord
    {
        return null;
    }
}
