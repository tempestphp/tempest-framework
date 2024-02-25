<?php

namespace Tests\Tempest\Container;

class ContainerObjectB
{
	public function __construct(public ContainerObjectA $a)
	{
	}
}
