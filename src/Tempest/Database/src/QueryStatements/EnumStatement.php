<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use BackedEnum;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use UnitEnum;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class EnumStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        /** @var class-string<UnitEnum|BackedEnum> */
        private string $enumClass,
        private bool $nullable = false,
        private null|UnitEnum|BackedEnum $default = null,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $cases = arr($this->enumClass::cases())
            ->map(fn (UnitEnum|BackedEnum $case) => ($case instanceof BackedEnum) ? $case->value : $case->name)
            ->map(fn (string $value) => "'{$value}'");

        if ($this->default !== null) {
            $defaultValue = ($this->default instanceof BackedEnum) ? $this->default->value : $this->default->name;
        } else {
            $defaultValue = null;
        }

        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf(
                '`%s` ENUM(%s) %s %s',
                $this->name,
                $cases->implode(', '),
                $defaultValue !== null ? "DEFAULT '{$defaultValue}'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            DatabaseDialect::SQLITE => sprintf(
                '`%s` TEXT %s %s',
                $this->name,
                $defaultValue !== null ? "DEFAULT '{$defaultValue}'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            DatabaseDialect::POSTGRESQL => sprintf(
                '`%s` %s %s %s',
                $this->name,
                str($this->enumClass)->replace('\\\\', '_'),
                $defaultValue !== null ? "DEFAULT (\"{$defaultValue}\")" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
        };
    }
}
