<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Construction;

use Tempest\Http\Route;

/**
 * @internal
 */
final class RouteTreeNode
{
    /** @var array<string, RouteTreeNode> */
    public array $staticPaths = [];

    /** @var array<string, RouteTreeNode> */
    public array $dynamicPaths = [];

    public ?MarkedRoute $targetRoute = null;

    private function __construct(
        public readonly RouteTreeNodeType $type,
        public readonly ?string $segment = null,
    ) {
    }

    public static function createRootRoute(): self
    {
        return new self(RouteTreeNodeType::Root);
    }

    public static function createDynamicRouteNode(string $regex): self
    {
        return new self(RouteTreeNodeType::Dynamic, $regex);
    }

    public static function createStaticRouteNode(string $name): self
    {
        return new self(RouteTreeNodeType::Static, $name);
    }

    public function findOrCreateNodeForSegment(string $routeSegment): self
    {
        // Translates a path segment like {id} into it's matching regex. Static segments remain the same
        $regexRouteSegment = self::convertDynamicSegmentToRegex($routeSegment);

        // Returns a static or dynamic child node depending on the segment is dynamic or static
        if ($routeSegment === $regexRouteSegment) {
            return $this->staticPaths[$regexRouteSegment] ??= self::createStaticRouteNode($routeSegment);
        }

        return $this->dynamicPaths[$regexRouteSegment] ??= self::createDynamicRouteNode($regexRouteSegment);
    }

    public function setTargetRoute(MarkedRoute $markedRoute): void
    {
        if ($this->targetRoute !== null) {
            throw new DuplicateRouteException($markedRoute->route);
        }

        $this->targetRoute = $markedRoute;
    }

    private static function convertDynamicSegmentToRegex(string $uriPart): string
    {
        $regex = '#\{'. Route::ROUTE_PARAM_NAME_REGEX . Route::ROUTE_PARAM_CUSTOM_REGEX .'\}#';

        return preg_replace_callback(
            $regex,
            static fn ($matches) => trim($matches[2] ?? Route::DEFAULT_MATCHING_GROUP),
            $uriPart,
        );
    }
}
