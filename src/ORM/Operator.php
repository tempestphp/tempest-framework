<?php

declare(strict_types=1);

namespace Tempest\ORM;

enum Operator: string
{
    case Equals = '=';
    case NotEquals = '!=';
    case GreaterThan = '>';
    case GreaterThanOrEquals = '>=';
    case LessThan = '<';
    case LessThanOrEquals = '<=';
    case Like = 'LIKE';
    case NotLike = 'NOT LIKE';
    case In = 'IN';
    case NotIn = 'NOT IN';
    case IsNull = 'IS NULL';
    case IsNotNull = 'IS NOT NULL';
    case Between = 'BETWEEN';
    case NotBetween = 'NOT BETWEEN';
    case Regexp = 'REGEXP';
    case NotRegexp = 'NOT REGEXP';
    case Exists = 'EXISTS';
    case NotExists = 'NOT EXISTS';
}
