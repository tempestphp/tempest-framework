<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

interface Migration
{
    public function getName(): string;

    public function up(): CreateTableStatement|null;

    public function down(): DropTableStatement|null;
}
