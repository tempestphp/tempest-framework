<?php

namespace Tempest\View\Export;

use Tempest\Support\Arr\ImmutableArray;

interface ExportableViewObject
{
    public ImmutableArray $exportData {
        get;
    }

    public static function restore(mixed ...$data): self;
}
