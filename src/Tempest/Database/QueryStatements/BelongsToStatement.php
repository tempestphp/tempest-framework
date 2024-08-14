<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class BelongsToStatement implements QueryStatement
{
    public function __construct(
        private string $local,
        private string $foreign,
        private string $onDelete = 'ON DELETE CASCADE',
        private string $onUpdate = 'ON UPDATE NO ACTION'
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        [$localTable, $localKey] = explode('.', $this->local);

        [$foreignTable, $foreignKey] = explode('.', $this->foreign);

        return match ($dialect) {
            DatabaseDialect::MYSQL,
            DatabaseDialect::POSTGRESQL => sprintf(
                'CONSTRAINT fk_%s_%s FOREIGN KEY %s(%s) REFERENCES %s(%s) %s %s',
                strtolower($foreignTable),
                strtolower($localTable),
                $localTable,
                $localKey,
                $foreignTable,
                $foreignKey,
                $this->onDelete,
                $this->onUpdate,
            ),
            DatabaseDialect::SQLITE => '',
        };
    }
}
