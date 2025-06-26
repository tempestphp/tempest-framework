<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Database\DialectWasNotSupported;

final readonly class ConstraintNameStatement implements QueryStatement
{
    public static function fromString(string $name): self
    {
        return new self(new IdentityStatement($name));
    }

    public function __construct(
        private IdentityStatement $name,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL, DatabaseDialect::POSTGRESQL => sprintf('CONSTRAINT %s', $this->name->compile($dialect)),
            default => throw new DialectWasNotSupported(),
        };
    }
}
