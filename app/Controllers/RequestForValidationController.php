<?php

declare(strict_types=1);

namespace App\Controllers;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\Between;

final class RequestForValidationController implements Request
{
    use IsRequest;

    public RequestItemForValidationController $item;

    #[Between(min: 1, max: 10)]
    public int $number;
}
