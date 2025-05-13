<?php

namespace Tempest\Console;

use Tempest\Support\IsEnumHelper;

enum GlobalFlags: string
{
    use IsEnumHelper;

    case FORCE = 'force';
    case HELP = 'help';
    case INTERACTION = 'interaction';
}
