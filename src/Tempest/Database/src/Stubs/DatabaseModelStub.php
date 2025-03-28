<?php

declare(strict_types=1);

namespace Tempest\Database\Stubs;

use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\Length;

final class DatabaseModelStub
{
    use IsDatabaseModel;

    public function __construct(
        #[Length(min: 1, max: 120)]
        public string $title,
    ) {}
}
