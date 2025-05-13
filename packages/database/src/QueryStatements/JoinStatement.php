<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use function Tempest\Support\str;

final readonly class JoinStatement implements QueryStatement
{
    public function __construct(
        private string $statement,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $statement = $this->statement;

        if (! str($statement)->lower()->startsWith(['join', 'inner join', 'left join', 'right join', 'full join', 'full outer join', 'self join'])) {
            $statement = sprintf('INNER JOIN %s', $statement);
        }

        return $statement;
    }
}
