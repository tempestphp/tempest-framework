<?php

namespace Tempest\Router;

interface RouteDecorator
{
    public function decorate(Route $route): Route;
}