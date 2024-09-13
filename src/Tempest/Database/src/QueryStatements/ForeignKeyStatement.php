<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class ForeignKeyStatement implements QueryStatement
{
    // TODO @treggats: not used yet
    public function __construct(
        private string $local,
        private string $foreign,
        private bool $ifExists = true,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        [, $localKey] = explode('.', $this->local);

        [$foreignTable, $foreignKey] = explode('.', $this->foreign);

        return match ($dialect) {
            DatabaseDialect::MYSQL,
            DatabaseDialect::POSTGRESQL => sprintf(
                'FOREIGN KEY %sfk_%s_%s_%s',
                $this->ifExists ? 'IF EXISTS ' : ' ',
                strtolower($foreignTable),
                strtolower($localKey),
                strtolower($foreignTable) . '_' . $foreignKey,
            ),
            DatabaseDialect::SQLITE => '',
        };
    }
}
