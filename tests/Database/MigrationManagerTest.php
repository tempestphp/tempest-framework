<?php

declare(strict_types=1);

use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\MigrationManager;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('migration', function () {
	$migrationManager = $this->container->get(MigrationManager::class);

	$migrationManager->up();
	$migrations = Migration::all();
	expect($migrations)->toHaveCount(3);

	$migrationManager->up();
	$migrations = Migration::all();
	expect($migrations)->toHaveCount(3);
});
