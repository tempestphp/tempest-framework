<?php

declare(strict_types=1);

namespace App\Controllers;

use Tempest\Validation\Rules\Between;

final class RequestItemForValidationController
{
    #[Between(min: 1, max: 10)]
    public int $number;
}
