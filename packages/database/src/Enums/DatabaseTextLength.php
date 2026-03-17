<?php

declare(strict_types=1);

namespace Tempest\Database\Enums;

enum DatabaseTextLength: int
{
    case TINY = 255;
    case DEFAULT = 65_535;
    case MEDIUM = 16_777_215;
    case LONG = 4_294_967_295;

    public static function fromLength(int $length): self
    {
        return match (true) {
            $length <= DatabaseTextLength::TINY->value => DatabaseTextLength::TINY,
            $length <= DatabaseTextLength::DEFAULT->value => DatabaseTextLength::DEFAULT,
            $length <= DatabaseTextLength::MEDIUM->value => DatabaseTextLength::MEDIUM,
            $length <= DatabaseTextLength::LONG->value => DatabaseTextLength::LONG,
            default => DatabaseTextLength::DEFAULT,
        };
    }

    public function toString(): string
    {
        return match ($this) {
            DatabaseTextLength::TINY => 'TINYTEXT',
            DatabaseTextLength::DEFAULT => 'TEXT',
            DatabaseTextLength::MEDIUM => 'MEDIUMTEXT',
            DatabaseTextLength::LONG => 'LONGTEXT',
        };
    }
}
