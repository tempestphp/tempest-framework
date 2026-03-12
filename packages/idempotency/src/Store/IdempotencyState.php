<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Store;

enum IdempotencyState: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
}
