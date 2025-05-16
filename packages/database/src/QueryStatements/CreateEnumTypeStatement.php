<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use BackedEnum;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use UnitEnum;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class CreateEnumTypeStatement implements QueryStatement
{
    public function __construct(
        /** @var class-string<UnitEnum|BackedEnum> */
        private string $enumClass,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $cases = arr($this->enumClass::cases())
            ->map(fn (UnitEnum|BackedEnum $case) => ($case instanceof BackedEnum) ? $case->value : $case->name)
            ->map(fn (string $value) => str_replace('\\', '\\\\', $value))
            ->map(fn (string $value) => "'{$value}'");

        return match ($dialect) {
            DatabaseDialect::MYSQL, DatabaseDialect::SQLITE => '',
            DatabaseDialect::POSTGRESQL => sprintf(
                <<<PSQL
                DO $$ BEGIN
                    CREATE TYPE %s AS (%s);
                EXCEPTION
                    WHEN duplicate_object THEN null;
                END $$;
                PSQL,
                str($this->enumClass)->replace('\\\\', '_'),
                $cases->implode(', '),
            ),
        };
    }
}
