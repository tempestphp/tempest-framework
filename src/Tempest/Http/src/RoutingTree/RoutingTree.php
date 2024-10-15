<?php

declare(strict_types=1);

namespace Tempest\Http\RoutingTree;

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

        $pathSegments = explode('/', $markedRoute->route->uri);
        array_shift($pathSegments);

        $root = $this->roots[$method->value] ??= RouteTreeNode::createRootRoute();
        $root->addPath($pathSegments, $markedRoute);
    }

    public function regexForMethod(Method $method): string
    {
        $root = $this->roots[$method->value] ?? RouteTreeNode::createRootRoute();
        return '#' . $root->toRegex() . '#';
    }
}