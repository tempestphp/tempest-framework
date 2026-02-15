<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\QueryStatement;

final readonly class TextStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
        private ?string $default = null,
        private ?int $length = null,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf(
                '`%s` %s %s',
                $this->name,
                $this->getSQLTypeDeclaration($this->length),
                $this->nullable ? '' : 'NOT NULL',
            ),
            default => sprintf(
                '`%s` TEXT %s %s',
                $this->name,
                $this->default !== null ? "DEFAULT '{$this->default}'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
        };
    }

    private function getSQLTypeDeclaration(?int $length = null): string
    {
        $type = match (true) {
            $length <= MysqlConfig::LIMIT_TINYTEXT => 'TINYTEXT',
            $length <= MysqlConfig::LIMIT_TEXT => 'TEXT',
            $length <= MysqlConfig::LIMIT_MEDIUMTEXT => 'MEDIUMTEXT',
            $length <= MysqlConfig::LIMIT_LONGTEXT => 'LONGTEXT',
            default => 'TEXT',
        };

        return $type;
    }
}
