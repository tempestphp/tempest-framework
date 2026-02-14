<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Support\Str;

final class TableGuesser
{
    private const array PREPOSITIONS = ['_to_', '_from_', '_in_'];

    public static function guess(string $migration): ?TableGuess
    {
        if (Str\starts_with($migration, 'create_')) {
            $table = Str\strip_end(Str\after_first($migration, 'create_'), '_table');

            return ! Str\is_empty($table) ? new TableGuess($table, isCreate: true) : null;
        }

        if (! Str\contains($migration, self::PREPOSITIONS)) {
            return null;
        }

        $table = Str\strip_end(Str\after_last($migration, self::PREPOSITIONS), '_table');

        return ! Str\is_empty($table) ? new TableGuess($table, isCreate: false) : null;
    }
}
