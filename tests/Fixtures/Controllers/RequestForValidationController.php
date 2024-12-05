<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\IsRequest;
use Tempest\Router\Request;
use Tempest\Validation\Rules\Between;

final class RequestForValidationController implements Request
{
    use IsRequest;

    public RequestItemForValidationController $item;

    #[Between(min: 1, max: 10)]
    public int $number;
}
