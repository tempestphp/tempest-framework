<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class DropTableStatement implements QueryStatement
{
    use CanExecuteStatement;

    public function __construct(
        private string $tableName,
        private array $constraints = [],
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $statements = [];

        foreach ($this->constraints as $constraint) {
            $statements[] = $constraint->compile($dialect);
        }

        $statements[] = sprintf('DROP TABLE IF EXISTS %s', $this->tableName);

        return implode('; ', $statements) . ';';
    }
}
