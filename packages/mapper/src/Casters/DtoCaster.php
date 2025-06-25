<?php

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\CannotCastValue;

use function Tempest\map;

final class DtoCaster implements Caster
{
    public function cast(mixed $input): mixed
    {
        if (! json_validate($input)) {
            throw new CannotCastValue('json string');
        }

        ['type' => $type, 'data' => $data] = json_decode($input, true);

        return map($data)->to($type);
    }
}
