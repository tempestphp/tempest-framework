<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\CommandBus\AsyncCommandRepositories\FileCommandRepository;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class AsyncCommandRepositoryInitializer implements Initializer
{
    public function initialize(Container $container): CommandRepository
    {
        // TODO: refactor to make it configurable

        return new FileCommandRepository();
    }
}
