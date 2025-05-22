<?php

namespace Tempest\Database;

use UnitEnum;

trait ChoosesDatabase
{
    private null|string|UnitEnum $inDatabase = null;

    public function inDatabase(null|string|UnitEnum $tag): self
    {
        $clone = clone $this;

        $clone->inDatabase = $tag;

        return $clone;
    }
}
