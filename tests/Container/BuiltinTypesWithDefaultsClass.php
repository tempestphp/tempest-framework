<?php

namespace Tests\Tempest\Container;

class BuiltinTypesWithDefaultsClass
{
	public function __construct(
		public string $aString = 'This is a default value',
	)
	{
	}
}
