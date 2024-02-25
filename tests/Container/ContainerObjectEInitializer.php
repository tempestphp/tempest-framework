<?php

namespace Tests\Tempest\Container;

use Tempest\Container\CanInitialize;
use Tempest\Container\Container;

class ContainerObjectEInitializer implements CanInitialize
{
	public function initialize(string $className, Container $container): ContainerObjectE
	{
		return new ContainerObjectE();
	}

	public function canInitialize(string $className): bool
	{
		return $className === ContainerObjectE::class;
	}
}
