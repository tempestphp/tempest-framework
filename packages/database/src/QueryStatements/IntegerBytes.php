<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

enum IntegerBytes: int
{
    case SMALL = 2;
    case DEFAULT = 4;
    case BIG = 8;

    public static function fromBytes(int $bytes): self
    {
        return match (true) {
            $bytes > self::DEFAULT->value => IntegerBytes::BIG,
            $bytes > self::SMALL->value => IntegerBytes::DEFAULT,
            DEFAULT => IntegerBytes::SMALL,
        };
    }

    public function toString(): string
    {
        return match($this) {
            IntegerBytes::SMALL => 'SMALLINT',
            IntegerBytes::DEFAULT => 'INTEGER',
            IntegerBytes::BIG => 'BIGINT',
        };
    }
}
