<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Construction;

use RuntimeException;
use Tempest\Http\Route;

final class RouteTreeNode
{
    /** @var array<string, RouteTreeNode> */
    private array $staticPaths = [];

    /** @var array<string, RouteTreeNode> */
    private array $dynamicPaths = [];

    /** @var ?MarkedRoute */
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

    public function addPath(array $pathSegments, MarkedRoute $route): void
    {
        if (count($pathSegments) === 0) {
            if ($this->leaf !== null) {
                throw new RuntimeException('Path already defined for' . $route->route->uri);
            }

            $this->leaf = $route;

            return;
        }

        $segment = array_shift($pathSegments);

        $dynamicSegment = self::convertDynamicSegmentToRegex($segment);

        if ($segment !== $dynamicSegment) {
            $node = $this->dynamicPaths[$dynamicSegment] ??= self::createParameterRoute($dynamicSegment);
        } else {
            $node = $this->staticPaths[$dynamicSegment] ??= self::createStaticRoute($segment);
        }

        $node->addPath($pathSegments, $route);
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

        if (count($this->staticPaths) > 0 || count($this->dynamicPaths) > 0) {
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
