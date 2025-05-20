<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Id;
use Tempest\Database\Query;

trait CanExecuteStatement
{
    public function execute(DatabaseDialect $dialect): Id|null
    {
        $sql = $this->compile($dialect);

        if (! $sql) {
            return null;
        }

        return new Query($sql)->execute();
    }
}
