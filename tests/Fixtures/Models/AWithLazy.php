<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\Lazy;
use Tempest\Database\IsModel;
use Tempest\Database\Model;

final class AWithLazy implements Model
{
    use IsModel;

    public static function table(): TableName
    {
        return new TableName('A');
    }

    public function __construct(
        #[Lazy]
        public B $b,
    ) {
    }
}
