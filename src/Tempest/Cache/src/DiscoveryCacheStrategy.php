<?php

declare(strict_types=1);

namespace Tempest\Cache;

enum DiscoveryCacheStrategy: string
{
    case ALL = 'all';
    case PARTIAL = 'partial';
    case NONE = 'none';
}
