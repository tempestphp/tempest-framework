<?php

declare(strict_types=1);

namespace Tempest\Database\Casters;

use Tempest\Core\Priority;
use Tempest\Database\PrimaryKey;
use Tempest\Mapper\Caster;

#[Priority(Priority::HIGHEST)]
final readonly class PrimaryKeyCaster implements Caster
{
    public static function for(): string
    {
        return PrimaryKey::class;
    }

    public function cast(mixed $input): PrimaryKey
    {
        if ($input instanceof PrimaryKey) {
            return $input;
        }

        return new PrimaryKey($input);
    }
}
