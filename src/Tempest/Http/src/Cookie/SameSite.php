<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

enum SameSite: string
{
    case STRICT = 'Strict';
    case LAX = 'Lax';
    case NONE = 'None';
}
