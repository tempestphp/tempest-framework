<?php

namespace Tempest\HttpApi;

use Attribute;
use Tempest\Http\Method;
use Tempest\Router\Route;

use function Tempest\Support\path;

#[Attribute]
class ApiResourceRoute implements Route
{
    /**
     * @param class-string $resourceClass
     * @param list<class-string<\Tempest\Router\HttpMiddleware>> $middleware
     */
    public function __construct(
        string $resourceClass,
        public Method $method,
        public string $uri = '',
        public array $middleware = [],
    ) {
        $resourceName = $resourceClass::getResourceUriName();
        $version = $resourceClass::getResourceApiVersion();
        $this->uri = path("/api/{$version}", $resourceName, $uri);
    }
}
