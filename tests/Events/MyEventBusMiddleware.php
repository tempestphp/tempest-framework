<?php

namespace Tests\Tempest\Events;

use Tempest\Events\EventBusMiddleware;

class MyEventBusMiddleware implements EventBusMiddleware
{
	public static bool $hit = false;

	public function __invoke(object $event, callable $next): void
	{
		self::$hit = true;

		$next($event);
	}
}
