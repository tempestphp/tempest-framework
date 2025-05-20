<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final class DropTableStatement implements QueryStatement
{
    use CanExecuteStatement;

    public function __construct(
        private readonly string $tableName,
        /** @var \Tempest\Database\QueryStatements\DropConstraintStatement[] $dropReferences */
        private array $dropReferences = [],
    ) {}

    public function dropReference(string $foreign): self
    {
        $this->dropReferences[] = new DropConstraintStatement($this->tableName, $foreign);

        return $this;
    }

    /** @param class-string $modelClass */
    public static function forModel(string $modelClass): self
    {
        return new self(new ModelDefinition($modelClass)->getTableDefinition()->name);
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $statements = [];

        foreach ($this->dropReferences as $dropReference) {
            $statements[] = $dropReference->compile($dialect);
        }

        $statements[] = match($dialect) {
            DatabaseDialect::POSTGRESQL => sprintf('DROP TABLE IF EXISTS `%s` CASCADE', $this->tableName),
            default => sprintf('DROP TABLE IF EXISTS `%s`', $this->tableName),
        };

        return implode('; ', $statements) . ';';
    }
}
