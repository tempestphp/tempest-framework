<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Virtual;

final class AWithVirtual implements DatabaseModel
{
    use IsDatabaseModel;

    #[Virtual]
    public int $fake {
        get => -$this->id->id;
    }

    public function __construct(
        public B $b,
    ) {}

    public static function table(): TableName
    {
        return new TableName('a');
    }
}
