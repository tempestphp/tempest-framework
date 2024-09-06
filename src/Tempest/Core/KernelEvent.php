<?php

declare(strict_types=1);

namespace Tempest\Core;

enum KernelEvent
{
    case BOOTED;
    case SHUTDOWN;
}
