<?php

declare(strict_types=1);

namespace Tempest\Http\RoutingTree;

enum RouteTreeNodeType
{
    case Root;
    case Static;
    case Parameter;
}
