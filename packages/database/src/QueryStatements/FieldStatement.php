<?php

namespace Tempest\Database\QueryStatements;

use Stringable;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

use function Tempest\Support\arr;

final readonly class FieldStatement implements QueryStatement
{
    public function __construct(
        private string|Stringable $field,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {


        return arr(explode(' AS ', (string) $this->field))
            ->map(function (string $part) use ($dialect) {
                return
                    arr(explode('.', $part))
                        ->map(fn (string $part) => trim($part, '` '))
                        ->map(fn (string $part) => match ($dialect) {
                            DatabaseDialect::SQLITE => $part,
                            default => sprintf('`%s`', $part),
                        })
                        ->implode('.');
            })
            ->implode(' AS ');
    }
}
