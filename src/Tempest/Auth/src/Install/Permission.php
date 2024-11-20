<?php

declare(strict_types=1);

namespace Tempest\Auth\Install;

use BackedEnum;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use UnitEnum;

final class Permission implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
    ) {
    }

    public function matches(string|UnitEnum|self $match): bool
    {
        $match = match(true) {
            is_string($match) => $match,
            $match instanceof BackedEnum => $match->value,
            $match instanceof UnitEnum => $match->name,
            default => $match->name,
        };

        return $this->name === $match;
    }
}
