<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\Eager;
use Tempest\Database\IsModel;
use Tempest\Database\Model;

final class BWithEager implements Model
{
    use IsModel;

    public static function table(): TableName
    {
        return new TableName('B');
    }

    public function __construct(
        #[Eager]
        public C $c,
    ) {
    }
}
