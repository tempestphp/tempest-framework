<?php

declare(strict_types=1);

namespace Tempest\Database;

interface Migration
{
    public function getName(): string;

    public function up(): Query|null;

    public function down(): Query|null;
}
