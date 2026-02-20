<?php

declare(strict_types=1);

namespace Tempest\Router;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\OpaqueRequest;
use Tempest\Http\Request;
use Tempest\Http\RequestHolder;
use Tempest\Http\Response;

use function Tempest\Mapper\map;

final readonly class WorkerRouter implements Router
{
    public function __construct(
        private Container $container,
        private Router $router,
        private RequestHolder $requestHolder,
    ) {}

    public function dispatch(Request|PsrRequest $request): Response
    {
        if (! $request instanceof Request) {
            $request = map($request)->with(PsrRequestToGenericRequestMapper::class)->do();
        }

        $this->requestHolder->setRequest($request);
        $this->container->singleton(Request::class, $this->container->get(OpaqueRequest::class));

        return $this->router->dispatch($request);
    }
}
