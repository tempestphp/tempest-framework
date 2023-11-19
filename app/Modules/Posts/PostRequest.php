<?php

declare(strict_types=1);

namespace App\Modules\Posts;

use Tempest\Http\BaseRequest;
use Tempest\Interface\Request;

final class PostRequest implements Request
{
    use BaseRequest;

    public string $title;

    public string $text;
}
