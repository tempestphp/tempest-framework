<?php

namespace Tempest\Database;

use UnitEnum;

trait OnDatabase
{
    private null|string|UnitEnum $onDatabase = null;

    public function onDatabase(null|string|UnitEnum $databaseTag): self
    {
        $clone = clone $this;

        $clone->onDatabase = $databaseTag;

        return $clone;
    }
}
