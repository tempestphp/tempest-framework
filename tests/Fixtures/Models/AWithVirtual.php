<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Virtual;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Builder\TableName;

final class AWithVirtual implements DatabaseModel
{
    use IsDatabaseModel;

    #[Virtual]
    public int $fake {
        get => $this->id->id * -1;
    }

    public function __construct(
        public B $b,
    ) {
    }

    public static function table(): TableName
    {
        return new TableName('a');
    }
}
