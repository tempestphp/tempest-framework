<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Requests;

use Tempest\Http\Request;
use Tempest\Http\Upload;

// TODO: Clean this up.
final class CreateBookRequest extends Request
{
    public string $title;

    public Upload $cover;
}
