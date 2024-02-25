<?php

namespace Tests\Tempest\CommandBus;

use Tempest\Commands\CommandBusMiddleware;

class MyCommandBusMiddleware implements CommandBusMiddleware
{
	public static bool $hit = false;

	public function __invoke(object $command, callable $next): void
	{
		self::$hit = true;

		$next($command);
	}
}
