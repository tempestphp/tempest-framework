<?php

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\ValueCouldNotBeCast;
use Tempest\Support\Json;

use function Tempest\map;

final class DtoCaster implements Caster
{
    public function cast(mixed $input): mixed
    {
        if (! Json\is_valid($input)) {
            throw new ValueCouldNotBeCast('json string');
        }

        ['type' => $type, 'data' => $data] = Json\decode($input);

        return map($data)->to($type);
    }
}
