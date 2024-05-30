<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Requests;

use Tempest\Validation\Rules\Length;

final class StoreBookRequest
{
    #[Length(min: 10, max: 120)]
    public string $title;
}
