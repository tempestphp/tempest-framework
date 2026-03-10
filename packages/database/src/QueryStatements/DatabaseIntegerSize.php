<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

enum DatabaseIntegerSize: int
{
    case SMALL = 2;
    case DEFAULT = 4;
    case BIG = 8;

    public static function fromBytes(int $bytes): self
    {
        return match (true) {
            $bytes > self::DEFAULT->value => self::BIG,
            $bytes > self::SMALL->value => self::DEFAULT,
            default => self::SMALL,
        };
    }

    public function toString(): string
    {
        return match ($this) {
            self::SMALL => 'SMALLINT',
            self::DEFAULT => 'INTEGER',
            self::BIG => 'BIGINT',
        };
    }
}
