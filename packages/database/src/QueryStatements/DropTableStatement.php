<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasLeadingStatements;
use Tempest\Database\QueryStatement;

use function Tempest\Database\model;

final class DropTableStatement implements QueryStatement, HasLeadingStatements
{
    use CanExecuteStatement;

    private(set) array $leadingStatements;

    public function __construct(
        private readonly string $tableName,
    ) {}

    public function dropReference(string $foreign): self
    {
        $this->leadingStatements[] = new DropConstraintStatement($this->tableName, $foreign);

        return $this;
    }

    /** @param class-string $modelClass */
    public static function forModel(string $modelClass): self
    {
        return new self(model($modelClass)->getTableDefinition()->name);
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::POSTGRESQL => sprintf('DROP TABLE IF EXISTS `%s` CASCADE', $this->tableName),
            default => sprintf('DROP TABLE IF EXISTS `%s`', $this->tableName),
        };
    }
}
