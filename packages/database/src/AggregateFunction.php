<?php

declare(strict_types=1);

namespace Tempest\Database;

enum AggregateFunction: string
{
    case SUM = 'SUM';
    case AVG = 'AVG';
    case MAX = 'MAX';
    case MIN = 'MIN';
}
