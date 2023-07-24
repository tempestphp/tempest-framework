<?php

declare(strict_types=1);

namespace App\Modules\Books\Requests;

use Tempest\Http\BaseRequest;
use Tempest\Interface\IsValidated;
use Tempest\Interface\Request;

final class CreateBookRequest implements Request, IsValidated
{
    use BaseRequest;

    public string $title;
}
