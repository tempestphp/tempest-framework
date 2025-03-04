<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class DropConstraintStatement implements QueryStatement
{
    public function __construct(
        private string $localTable,
        private string $foreign,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        [$foreignTable] = explode('.', $this->foreign);

        $constraintName = sprintf(
            'fk_%s_%s',
            strtolower($foreignTable),
            strtolower($this->localTable),
        );

        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf(
                'ALTER TABLE `%s` DROP CONSTRAINT %s',
                $foreignTable,
                $constraintName,
            ),
            default => '',
        };
    }
}
