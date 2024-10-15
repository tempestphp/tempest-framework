<?php

declare(strict_types=1);

namespace Tempest\Http\RoutingTree;


use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use RuntimeException;
use Tempest\Http\GenericRouter;
use Tempest\Http\Route;

final class RouteTreeNode
{
    /** @var array<string, RouteTreeNode> */
    private array $staticPaths = [];

    private ?RouteTreeNode $dynamicPath = null;

    /** @var ?MarkedRoute */
    private ?MarkedRoute $leaf = null;

    private function __construct(
        public readonly RouteTreeNodeType $type,
        public readonly ?string $name = null
    )
    {
    }

    public static function createRootRoute(): self
    {
        return new self(RouteTreeNodeType::Root);
    }

    public static function createParameterRoute(): self
    {
        return new self(RouteTreeNodeType::Parameter);
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
        $isDynamic = self::isDynamicRouteSegment($segment);

        if ($isDynamic) {
            if ($this->dynamicPath === null) {
                $this->dynamicPath = self::createParameterRoute();
            }

            $this->dynamicPath->addPath($pathSegments, $route);
            return;
        }

        if (!isset($this->staticPaths[$segment])) {
            $this->staticPaths[$segment] = self::createStaticRoute($segment);
        }

        $this->staticPaths[$segment]->addPath($pathSegments, $route);
    }

    private function regexSegment(): string
    {
        return match($this->type) {
            RouteTreeNodeType::Root => '',
            RouteTreeNodeType::Static => "/{$this->name}",
            RouteTreeNodeType::Parameter => '/([^/]++)',
        };
    }

    public function toRegex(): string
    {
        $regexp = $this->regexSegment();

        if ($this->dynamicPath !== null || count($this->staticPaths) > 0 ) {
            $regexp .= "(?";

            foreach ($this->staticPaths as $path) {
                $regexp .= '|' . $path->toRegex();
            }
            if ($this->dynamicPath !== null) {
                $regexp .= '|' . $this->dynamicPath->toRegex();
            }

            if ($this->leaf !== null) {
                $regexp .= '|(*' . GenericRouter::REGEX_MARK_TOKEN . ':' . $this->leaf->mark . ')';
            }

            $regexp .= ")";
        } else if ($this->leaf !== null) {
            $regexp .= '(*' . GenericRouter::REGEX_MARK_TOKEN . ':' . $this->leaf->mark . ')';
        }

        return $regexp;
    }

    private static function isDynamicRouteSegment(string $pathSegment): bool
    {
        $regexp = '#\{' . Route::ROUTE_PARAM_NAME_REGEX . Route::ROUTE_PARAM_CUSTOM_REGEX . '\}#';
        return preg_match($regexp, $pathSegment) === 1;
    }
}
