<?php

namespace Tests\Tempest\Container;

class OptionalTypesClass
{
	public function __construct(
		public ?string $aString
	)
	{
	}
}
