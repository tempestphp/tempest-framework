<?php

namespace Tempest\Console;

use Tempest\Support\IsEnumHelper;

enum GlobalFlags: string
{
    use IsEnumHelper;

    case FORCE = 'force';
    case FORCE_SHORTHAND = '-f';
    case HELP = 'help';
    case HELP_SHORTHAND = '-h';
    case INTERACTION = 'interaction';
}
