<?php

declare(strict_types=1);

namespace App\Modules\Form;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\NotEmpty;

final class FormRequest implements Request
{
    use IsRequest;

    #[NotEmpty]
    public string $name;
}
