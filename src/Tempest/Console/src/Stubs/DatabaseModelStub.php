<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\Length;

final class DatabaseModelStub implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        #[Length(min: 1, max: 120)]
        public string $title
    ) {
    }
}
