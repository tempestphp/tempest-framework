<?php

declare(strict_types=1);

namespace App\Modules\Books\Requests;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\IsValidated;

final class CreateBookRequest implements Request, IsValidated
{
    use IsRequest;

    public string $title;
}
