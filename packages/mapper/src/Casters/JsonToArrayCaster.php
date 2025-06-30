<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Support\Json;

final class JsonToArrayCaster implements Caster
{
    public function cast(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        return Json\decode($input);
    }
}
