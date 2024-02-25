<?php

namespace Tests\Tempest\Container;

use DateTime;

class UnionTypesClass
{
	public function __construct(
		public DateTime $aStringOrDate
	)
	{
	}
}
