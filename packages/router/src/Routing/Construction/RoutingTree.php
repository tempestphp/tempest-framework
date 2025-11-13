<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Construction;

use Tempest\Router\Routing\Matching\MatchingRegex;

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
        // @mago-expect lint:no-multi-assignments
        $node = $this->roots[$method->value] ??= RouteTreeNode::createRootRoute();

        $segments = $markedRoute->route->split();

        // Traverse the tree and find the node for each route segment
        foreach ($segments as $index => $routeSegment) {
            $isOptional = $this->isOptionalSegment($routeSegment);

            if ($isOptional) {
                $node->setTargetRoute($markedRoute);
                $routeSegment = $this->stripOptionalMarker($routeSegment);
            }

            $node = $node->findOrCreateNodeForSegment($routeSegment);
        }

        $node->setTargetRoute($markedRoute);
    }

    private function isOptionalSegment(string $segment): bool
    {
        return str_contains($segment, '?');
    }

    private function stripOptionalMarker(string $segment): string
    {
        return str_replace('?', '', $segment);
    }

    /** @return array<string, MatchingRegex> */
    public function toMatchingRegexes(): array
    {
        return array_map(static fn (RouteTreeNode $node) => new RouteMatchingRegexBuilder($node)->toRegex(), $this->roots);
    }
}
