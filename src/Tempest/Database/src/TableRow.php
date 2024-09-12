<?php

declare(strict_types=1);

namespace Tempest\Database;

interface TableRow
{
    public function getDefinition(): string;
}
