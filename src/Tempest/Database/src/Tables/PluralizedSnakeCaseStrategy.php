<?php

declare(strict_types=1);

namespace Tempest\Database\Tables;

final class PluralizedSnakeCaseStrategy implements NamingStrategy
{
    public function getName(string $model): string
    {
        return (string) str(class_basename($model))->snake()->pluralStudly();
    }
}
