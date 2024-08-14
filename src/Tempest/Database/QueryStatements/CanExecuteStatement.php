<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\Query;

trait CanExecuteStatement
{
    public function execute(DatabaseDialect $dialect): mixed
    {
        return (new Query($this->compile($dialect)))->execute();
    }
}
