<?php

declare(strict_types=1);

namespace Tempest\Idempotency;

interface HasIdempotencyKey
{
    public function getIdempotencyKey(): string;
}
