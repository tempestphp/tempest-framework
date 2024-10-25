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

    public static function createParameterRoute(string $regex): self
    {
        return new self(RouteTreeNodeType::Parameter, $regex);
    }

    public static function createStaticRoute(string $name): self
    {
        return new self(RouteTreeNodeType::Static, $name);
    }

    public function addPath(array $pathSegments, MarkedRoute $markedRoute): void
    {
        if ($pathSegments === []) {
            if ($this->leaf !== null) {
                throw new DuplicateRouteException($markedRoute->route);
            }

            $this->leaf = $markedRoute;

            return;
        }

        $currentPathSegment = array_shift($pathSegments);

        $regexPathSegment = self::convertDynamicSegmentToRegex($currentPathSegment);

        if ($currentPathSegment !== $regexPathSegment) {
            $node = $this->dynamicPaths[$regexPathSegment] ??= self::createParameterRoute($regexPathSegment);
        } else {
            $node = $this->staticPaths[$regexPathSegment] ??= self::createStaticRoute($currentPathSegment);
        }

        $node->addPath($pathSegments, $markedRoute);
    }

    public static function convertDynamicSegmentToRegex(string $uriPart): string
    {
        $regex = '#\{'. Route::ROUTE_PARAM_NAME_REGEX . Route::ROUTE_PARAM_CUSTOM_REGEX .'\}#';

        return preg_replace_callback(
            $regex,
            static fn ($matches) => trim($matches[2] ?? Route::DEFAULT_MATCHING_GROUP),
            $uriPart,
        );
    }

    private function regexSegment(): string
    {
        return match($this->type) {
            RouteTreeNodeType::Root => '^',
            RouteTreeNodeType::Static => "/{$this->segment}",
            RouteTreeNodeType::Parameter => '/(' . $this->segment . ')',
        };
    }

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
}
