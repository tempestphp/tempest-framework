<?php

declare(strict_types=1);

namespace App\Modules\Books\Requests;

use Tempest\Http\BaseRequest;
use Tempest\Interfaces\IsValidated;
use Tempest\Interfaces\Request;

final class CreateBookRequest implements Request, IsValidated
{
    use BaseRequest;

    public string $title;
}
