<?php

declare(strict_types=1);

namespace Tempest\Database\Tables;

interface NamingStrategy
{
    /** @var class-string<\Tempest\Database\DatabaseModel> $model */
    public function getName(string $model): string;
}
