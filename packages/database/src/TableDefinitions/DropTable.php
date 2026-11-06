<?php

namespace Tempest\Database\TableDefinitions;

final readonly class DropTable implements TableDefinition
{
    public function __construct(
        public string $name,
    ) {}
}