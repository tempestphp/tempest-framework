<?php

declare(strict_types=1);

use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Http\RouteConfig;

test('discovery', function () {
	$kernel = new Kernel(__DIR__ . '/../', new AppConfig());

	$container = $kernel->init();

	$config = $container->get(RouteConfig::class);

	expect(count($config->routes) > 1)->toBeTrue();
});
