<?php

declare(strict_types=1);

use Tempest\AppConfig;
use Tests\Tempest\Console\Commands\MyDiscovery;
use Tests\Tempest\TestCase;

uses(TestCase::class);

it('clears discovery cache', function () {
	$appConfig = $this->container->get(AppConfig::class);

	MyDiscovery::$cacheCleared = false;

	$appConfig->discoveryClasses = [MyDiscovery::class];

	$this->console('discovery:clear');

	expect(MyDiscovery::$cacheCleared)->toBeTrue();
});
