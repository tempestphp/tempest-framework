<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Enums\DatabaseTextLength;
use Tempest\Database\QueryStatement;

final readonly class TextStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
        private int|DatabaseTextLength $length = DatabaseTextLength::DEFAULT,
        private ?string $default = null,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $name = $dialect->quoteIdentifier($this->name);

        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf(
                '%s %s %s',
                $name,
                $this->getSQLTypeDeclaration($this->length),
                $this->nullable ? '' : 'NOT NULL',
            ),
            default => sprintf(
                '%s TEXT %s %s',
                $name,
                $this->default !== null ? "DEFAULT '{$this->default}'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
        };
    }

    private function getSQLTypeDeclaration(int|DatabaseTextLength $length): string
    {
        if ($length instanceof DatabaseTextLength) {
            return $length->toString();
        }

        return DatabaseTextLength::fromLength($length)->toString();
    }
}
