<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use BackedEnum;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use UnitEnum;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class DropEnumTypeStatement implements QueryStatement
{
    public function __construct(
        /** @var class-string<UnitEnum|BackedEnum> */
        private string $enumClass,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL, DatabaseDialect::SQLITE => '',
            DatabaseDialect::POSTGRESQL => sprintf(
                <<<'PSQL'
                DROP TYPE IF EXISTS "%s";
                PSQL,
                str($this->enumClass)->replace('\\\\', '_'),
            ),
        };
    }
}
