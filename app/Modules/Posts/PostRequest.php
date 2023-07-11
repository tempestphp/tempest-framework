<?php

namespace App\Modules\Posts;

use Tempest\Http\BaseRequest;
use Tempest\Interfaces\Request;

final class PostRequest implements Request
{
    use BaseRequest;

    public string $title;

    public string $text;
}
