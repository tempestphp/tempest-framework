<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Construction;

/**
 * @internal
 */
final class RoutingTree
{
    /** @var array<string, RouteTreeNode> */
    private array $roots;

    public function __construct()
    {
        $this->roots = [];
    }

    public function add(MarkedRoute $markedRoute): void
    {
        $method = $markedRoute->route->method;

        // Find the root tree node based on HTTP method
        $root = $this->roots[$method->value] ??= RouteTreeNode::createRootRoute();

        // Add path to tree using recursion
        $root->addPath($markedRoute->route->split(), $markedRoute);
    }

    /** @return array<string, string> */
    public function toMatchingRegexes(): array
    {
        return array_map(static fn (RouteTreeNode $node) => "#{$node->toRegex()}#", $this->roots);
    }
}
