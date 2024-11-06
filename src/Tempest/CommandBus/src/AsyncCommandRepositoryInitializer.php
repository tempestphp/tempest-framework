<?php

namespace Tempest\CommandBus;

use Tempest\CommandBus\AsyncCommandRepositories\FileRepository;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class AsyncCommandRepositoryInitializer implements Initializer
{
    public function initialize(Container $container): AsyncCommandRepository
    {
        // TODO: refactor to make it configurable

        return new FileRepository();
    }
}