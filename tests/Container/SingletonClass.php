<?php

namespace Tests\Tempest\Container;

class SingletonClass
{
	public static int $count = 0;

	public function __construct()
	{
		self::$count += 1;
	}
}
