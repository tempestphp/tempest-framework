<?php

namespace Tempest\Interfaces;

interface Entity
{
    /** @return Query<static> */
    public static function query(): Query;
}
