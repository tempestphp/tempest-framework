<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Request;
use Tempest\Validation\Rules\Between;

// TODO: Clean this up.
final class RequestForValidationController extends Request
{
    public RequestItemForValidationController $item;

    #[Between(min: 1, max: 10)]
    public int $number;
}
