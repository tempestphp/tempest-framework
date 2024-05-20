<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use Tempest\Auth\HasIdentity;
use Tempest\Auth\Identifiable;
use Tempest\Database\IsModel;
use Tempest\Database\Model;

final class User implements Identifiable, Model
{
    use HasIdentity;
    use IsModel;

    public function __construct(
        public string $name,
        public string $email = '',
        public string $password = '',
    ) {
    }
}
