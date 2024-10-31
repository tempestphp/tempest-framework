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
    private array $staticPaths = [];

    /** @var array<string, RouteTreeNode> */
    private array $dynamicPaths = [];

    private ?MarkedRoute $leaf = null;

    private function __construct(
        public readonly RouteTreeNodeType $type,
        public readonly ?string $segment = null
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

    public function addPath(array $pathSegments, MarkedRoute $markedRoute): void
    {
        // If path segments is empty this node should target to given marked route
        if ($pathSegments === []) {
            if ($this->leaf !== null) {
                throw new DuplicateRouteException($markedRoute->route);
            }

            $this->leaf = $markedRoute;

            return;
        }

        // Removes the first element of the pathSegments and use it to determin the next routing node
        $currentPathSegment = array_shift($pathSegments);

        // Translates a path segment like {id} into it's matching regex. Static segments remain the same
        $regexPathSegment = self::convertDynamicSegmentToRegex($currentPathSegment);

        // Find or create the next node to recurse into
        if ($currentPathSegment !== $regexPathSegment) {
            $node = $this->dynamicPaths[$regexPathSegment] ??= self::createDynamicRouteNode($regexPathSegment);
        } else {
            $node = $this->staticPaths[$regexPathSegment] ??= self::createStaticRouteNode($currentPathSegment);
        }

        // Recurse into the newly created node to add the remainder of the path segments
        $node->addPath($pathSegments, $markedRoute);
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

    /**
     * Return the matching regex of this path and it's children by means of recursion
     */
    public function toRegex(): string
    {
        $regexp = $this->regexSegment();

        if ($this->staticPaths !== [] || $this->dynamicPaths !== []) {
            $regexp .= "(?";

            foreach ($this->staticPaths as $path) {
                $regexp .= '|' . $path->toRegex();
            }

            foreach ($this->dynamicPaths as $path) {
                $regexp .= '|' . $path->toRegex();
            }

            if ($this->leaf !== null) {
                $regexp .= '|\/?$(*' . MarkedRoute::REGEX_MARK_TOKEN . ':' . $this->leaf->mark . ')';
            }

            $regexp .= ")";
        } elseif ($this->leaf !== null) {
            $regexp .= '\/?$(*' . MarkedRoute::REGEX_MARK_TOKEN . ':' . $this->leaf->mark . ')';
        }

        return $regexp;
    }

    /**
     * Translates the only current node segment into regex. This does not recurse into it's child nodes.
     */
    private function regexSegment(): string
    {
        return match($this->type) {
            RouteTreeNodeType::Root => '^',
            RouteTreeNodeType::Static => "/{$this->segment}",
            RouteTreeNodeType::Dynamic => '/(' . $this->segment . ')',
        };
    }
}
