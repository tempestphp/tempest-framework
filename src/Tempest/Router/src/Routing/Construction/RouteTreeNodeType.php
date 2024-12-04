<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Construction;

enum RouteTreeNodeType
{
    case Root;
    case Static;
    case Dynamic;
}
