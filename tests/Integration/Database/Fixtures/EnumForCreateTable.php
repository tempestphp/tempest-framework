<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

enum EnumForCreateTable: string
{
    case FOO = 'foo';
    case BAR = 'bar';
    case SELF = self::class;
}
