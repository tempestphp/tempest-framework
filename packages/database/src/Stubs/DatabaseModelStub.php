<?php

declare(strict_types=1);

namespace Tempest\Database\Stubs;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Validation\Rules\HasLength;

final class DatabaseModelStub
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        #[HasLength(min: 1, max: 120)]
        public string $title,
    ) {}
}
