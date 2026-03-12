<?php

declare(strict_types=1);

namespace Tempest\Database\Enums;

enum DatabaseTextLength: int
{
    case TINY = 255;
    case DEFAULT = 65535;
    case MEDIUM = 16777215;
    case LONG = 4294967295;

    public static function fromLength(int $length): self
    {
        return match (true) {
            $length <= DatabaseTextLength::TINY => DatabaseTextLength::TINY,
            $length <= DatabaseTextLength::DEFAULT => DatabaseTextLength::DEFAULT,
            $length <= DatabaseTextLength::MEDIUM => DatabaseTextLength::MEDIUM,
            $length <= DatabaseTextLength::LONG => DatabaseTextLength::LONG,
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
