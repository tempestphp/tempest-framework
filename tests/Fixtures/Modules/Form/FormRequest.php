<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Form;

use Tempest\Http\Request;
use Tempest\Validation\Rules\Between;
use Tempest\Validation\Rules\NotEmpty;

// TODO: Clean this up. It shouldn't extend the request.
final class FormRequest extends Request
{
    #[NotEmpty]
    public string $name;

    #[Between(min: 10, max: 15)]
    public int $number;
}
