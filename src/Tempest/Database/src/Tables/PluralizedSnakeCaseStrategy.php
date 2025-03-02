<?php

declare(strict_types=1);

namespace Tempest\Database\Tables;

use function Tempest\Support\str;

final class PluralizedSnakeCaseStrategy implements NamingStrategy
{
    public function getName(string $model): string
    {
        return (string) str($model)
            ->classBasename()
            ->snake()
            ->pluralizeLastWord();
    }
}
