<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class VarcharStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private int $size = 255,
        private bool $nullable = false,
        private ?string $default = null,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            '`%s` VARCHAR(%s) %s %s',
            $this->name,
            $this->size,
            $this->default !== null ? "DEFAULT '{$this->default}'" : '',
            $this->nullable ? '' : 'NOT NULL',
        );
    }
}
