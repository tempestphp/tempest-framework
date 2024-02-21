<?php

declare(strict_types=1);

namespace Tempest\Interface;

use Tempest\Database\Query;

interface Migration
{
    public function getName(): string;

    public function up(): Query|null;

    public function down(): Query|null;
}
