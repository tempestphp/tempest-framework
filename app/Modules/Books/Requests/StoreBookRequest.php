<?php

declare(strict_types=1);

namespace App\Modules\Books\Requests;

use Tempest\Interface\IsValidated;
use Tempest\Validation\Rules\Length;

final class StoreBookRequest implements IsValidated
{
    #[Length(min: 10, max: 120)]
    public string $title;
}
