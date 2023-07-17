<?php

namespace App\Modules\Books;

use Tempest\Http\BaseRequest;
use Tempest\Interfaces\Request;

final class StoreBookRequest implements Request
{
    use BaseRequest;

    public string $title;
}