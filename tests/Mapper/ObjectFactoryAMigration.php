<?php

namespace Tests\Tempest\Mapper;

use Tempest\Database\Migration;
use Tempest\Database\Query;

class ObjectFactoryAMigration implements Migration
{
	public function getName(): string
	{
		return 'object-a';
	}

	public function up(): Query|null
	{
		return new Query("CREATE TABLE ObjectFactoryA (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `prop` TEXT
        )");
	}

	public function down(): Query|null
	{
		return null;
	}
}
