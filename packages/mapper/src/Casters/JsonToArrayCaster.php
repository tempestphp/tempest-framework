<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Context;
use Tempest\Support\Json;

#[Context(Context::DEFAULT)]
#[Priority(Priority::NORMAL)]
final class JsonToArrayCaster implements Caster
{
    public static function for(): string
    {
        return 'array';
    }

    public function cast(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        return Json\decode($input);
    }
}
