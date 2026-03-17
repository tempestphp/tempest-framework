<?php

declare(strict_types=1);

namespace Tempest\Core;

enum InsightType: string
{
    case ERROR = 'error';
    case SUCCESS = 'success';
    case NORMAL = 'normal';
    case WARNING = 'warning';
}
