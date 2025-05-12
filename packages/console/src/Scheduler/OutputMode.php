<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

enum OutputMode: string
{
    case OVERWRITE = '>';
    case APPEND = '>>';
}
