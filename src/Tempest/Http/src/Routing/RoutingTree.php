<?php

declare(strict_types=1);

namespace Tempest\Http\Routing;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\Method;
use Tempest\Http\Route;

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

    public function regexForMethod(Method $method): string
    {
        $root = $this->roots[$method->value] ?? RouteTreeNode::createRootRoute();
        return '#^' . $root->toRegex() . '$#';
    }
}