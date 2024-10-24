<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Construction;

final class RoutingTree
{
    /** @var array<string, RouteTreeNode>  */
    private array $roots;

    public function __construct() {
        $this->roots = [];
    }

    public function add(MarkedRoute $markedRoute): void
    {
        $method = $markedRoute->route->method;

        $root = $this->roots[$method->value] ??= RouteTreeNode::createRootRoute();
        $root->addPath($markedRoute->route->routeParts(), $markedRoute);
    }

    /** @return array<string, string> */
    public function toMatchingRegexes(): array
    {
        return array_map(static fn(RouteTreeNode $node) => "#{$node->toRegex()}#", $this->roots);
    }
}