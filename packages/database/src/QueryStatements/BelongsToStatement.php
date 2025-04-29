<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Database\UnsupportedDialect;

final readonly class BelongsToStatement implements QueryStatement
{
    public function __construct(
        private string $local,
        private string $foreign,
        private OnDelete $onDelete = OnDelete::RESTRICT,
        private OnUpdate $onUpdate = OnUpdate::NO_ACTION,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        [$localTable, $localKey] = explode('.', $this->local);
        [$foreignTable, $foreignKey] = explode('.', $this->foreign);

        return match ($dialect) {
            DatabaseDialect::MYSQL, DatabaseDialect::POSTGRESQL => new ConstraintStatement(
                ConstraintNameStatement::fromString(
                    sprintf(
                        'fk_%s_%s_%s',
                        strtolower($foreignTable),
                        strtolower($localTable),
                        strtolower($localKey),
                    ),
                ),
                new RawStatement(
                    sprintf(
                        'FOREIGN KEY %s(%s) REFERENCES %s(%s) %s %s',
                        $localTable,
                        $localKey,
                        $foreignTable,
                        $foreignKey,
                        'ON DELETE ' . $this->onDelete->value,
                        'ON UPDATE ' . $this->onUpdate->value,
                    ),
                ),
            )->compile($dialect),
            DatabaseDialect::SQLITE => throw new UnsupportedDialect(),
        };
    }
}
