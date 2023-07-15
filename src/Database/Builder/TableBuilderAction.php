<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

enum TableBuilderAction
{
    case DROP;
    case CREATE;
    case ALTER;
}
