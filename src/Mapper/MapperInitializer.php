<?php

namespace Tempest\Mapper;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class MapperInitializer implements Initializer
{
    public function initialize(Container $container): ObjectMapper
    {
        return new ObjectMapper([
            new PsrRequestToRequestMapper(),
            new RequestToPsrRequestMapper(),
            new ArrayToObjectMapper(),
            new QueryToModelMapper(),
            new ModelToQueryMapper(),
            new RequestToObjectMapper(),
        ]);
    }
}