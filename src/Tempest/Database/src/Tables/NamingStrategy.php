<?php

declare(strict_types=1);

namespace Tempest\Database\Tables;

interface NamingStrategy
{
    /** @param class-string<\Tempest\Database\DatabaseModel> $model */
    public function getName(string $model): string;
}
