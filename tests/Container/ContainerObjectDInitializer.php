<?php

namespace Tests\Tempest\Container;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

class ContainerObjectDInitializer implements Initializer
{
	public function initialize(string $className, Container $container): ContainerObjectD
	{
		return new ContainerObjectD(prop: 'test');
	}
}
