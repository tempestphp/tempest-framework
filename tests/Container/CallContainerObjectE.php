<?php

namespace Tests\Tempest\Container;

class CallContainerObjectE
{
	public function method(ContainerObjectE $input): ContainerObjectE
	{
		return $input;
	}
}
