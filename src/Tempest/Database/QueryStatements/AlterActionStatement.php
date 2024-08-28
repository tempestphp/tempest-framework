<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class AlterActionStatement implements QueryStatement
{
    public function __construct(
        private AlterStatement $action,
        private QueryStatement $statement,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            '%s %s',
            match ($dialect) {
                DatabaseDialect::MYSQL => $this->action->value,
                DatabaseDialect::POSTGRESQL,
                DatabaseDialect::SQLITE => $this->action->value . ' COLUMN',
            },
            $this->statement->compile($dialect)
        );
    }
}
