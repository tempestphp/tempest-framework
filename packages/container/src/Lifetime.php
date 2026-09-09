<?php

declare(strict_types=1);

namespace Tempest\Container;

enum Lifetime
{
    /** Keep a singleton alive as long as the process is running */
    case PROCESS;

    /** Keep a singleton alive within the lifetime of a request */
    case REQUEST;
}
