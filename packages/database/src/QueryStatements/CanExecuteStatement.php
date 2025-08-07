<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use UnitEnum;

trait CanExecuteStatement
{
    public function execute(DatabaseDialect $dialect, null|string|UnitEnum $onDatabase): ?PrimaryKey
    {
        $sql = $this->compile($dialect);

        if (! $sql) {
            return null;
        }

        return new Query($sql)
            ->onDatabase($onDatabase)
            ->execute();
    }
}
