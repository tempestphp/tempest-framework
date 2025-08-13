<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Form;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\Rules\IsNotEmptyString;

final class FormRequest implements Request
{
    use IsRequest;

    #[IsNotEmptyString]
    public string $name;

    #[IsBetween(min: 10, max: 15)]
    public int $number;
}
