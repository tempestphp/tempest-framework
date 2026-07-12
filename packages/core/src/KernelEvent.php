<?php

declare(strict_types=1);

namespace Tempest\Core;

enum KernelEvent
{
    case SHUTTING_DOWN;
    case RESETTING;
    case RESET;
    case BOOTED;
    case SHUTDOWN;
}
