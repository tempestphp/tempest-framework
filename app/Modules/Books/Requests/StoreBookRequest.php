<?php

declare(strict_types=1);

namespace App\Modules\Books\Requests;

use Tempest\Http\BaseRequest;
use Tempest\Interfaces\Request;

final class StoreBookRequest implements Request
{
    use BaseRequest;

    public string $title;
}
