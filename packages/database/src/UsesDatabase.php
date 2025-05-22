<?php

namespace Tempest\Database;

use UnitEnum;

trait UsesDatabase
{
    private null|string|UnitEnum $useDatabase = null;

    public function useDatabase(null|string|UnitEnum $databaseTag): self
    {
        $clone = clone $this;

        $clone->useDatabase = $databaseTag;

        return $clone;
    }
}
