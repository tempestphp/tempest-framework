<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Id;
use Tempest\Database\Query;
use UnitEnum;

trait CanExecuteStatement
{
    public function execute(DatabaseDialect $dialect, null|string|UnitEnum $onDatabase): ?Id
    {
        $sql = $this->compile($dialect);

        if (! $sql) {
            return null;
        }

        return new Query($sql)->onDatabase($onDatabase)->execute();
    }
}
