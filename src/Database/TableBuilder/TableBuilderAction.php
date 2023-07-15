<?php

declare(strict_types=1);

namespace Tempest\Database\TableBuilder;

enum TableBuilderAction
{
    case DROP;
    case CREATE;
    case ALTER;
}
