<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

enum OutputType: string
{
    case Overwrite = ">";
    case Append = ">>";
}
