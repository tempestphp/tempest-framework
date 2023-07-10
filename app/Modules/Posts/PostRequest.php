<?php

namespace App\Modules\Posts;

use Tempest\Interfaces\Request;
use Tempest\Route\BaseRequest;

final class PostRequest implements Request
{
    use BaseRequest;

    public string $title;

    public string $text;
}