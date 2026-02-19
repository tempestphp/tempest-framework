<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Contracts;

interface HasIdempotencyKey
{
    public function getIdempotencyKey(): string;
}
