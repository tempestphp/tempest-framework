<?php

declare(strict_types=1);

namespace Tempest\Database;

interface AbstractDatabaseModel
{
	public static function getModelInstanceClass(mixed ...$params): string;
}