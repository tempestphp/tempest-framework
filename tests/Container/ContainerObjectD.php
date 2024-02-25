<?php

namespace Tests\Tempest\Container;

use Tempest\Container\InitializedBy;

#[InitializedBy(ContainerObjectDInitializer::class)]
class ContainerObjectD
{
	public function __construct(public string $prop)
	{
	}
}
