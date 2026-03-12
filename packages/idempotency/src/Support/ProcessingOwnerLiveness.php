<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Support;

enum ProcessingOwnerLiveness
{
    case ALIVE;
    case DEAD;
    case UNKNOWN;
}
