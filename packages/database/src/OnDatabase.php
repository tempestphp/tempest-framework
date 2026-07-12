<?php

namespace Tempest\Database;

use UnitEnum;

trait OnDatabase
{
    private(set) string|UnitEnum|null $onDatabase = null;

    public function onDatabase(string|UnitEnum|null $databaseTag): self
    {
        $clone = clone $this;

        $clone->onDatabase = $databaseTag;

        return $clone;
    }
}
