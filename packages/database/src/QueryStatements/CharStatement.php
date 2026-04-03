<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\DefaultValueWasInvalid;
use Tempest\Database\QueryStatement;

final readonly class CharStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private ?int $size = null,
        private bool $nullable = false,
        private ?string $default = null,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->size !== null && $this->default !== null && $this->size < mb_strlen($this->default)) {
            throw new DefaultValueWasInvalid($this->name, $this->default);
        }

        return sprintf(
            '%s CHAR(%s)%s%s',
            $dialect->quoteIdentifier($this->name),
            $this->determineSize(),
            $this->default !== null ? " DEFAULT '{$this->default}'" : '',
            $this->nullable ? '' : ' NOT NULL',
        );
    }

    public function determineSize(): int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if ($this->default !== null) {
            return mb_strlen($this->default);
        }

        return 1;
    }
}
