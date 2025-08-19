<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Requests;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\ErrorBag;
use Tempest\Validation\Rules\IsEmail;
use Tempest\Validation\Rules\IsNotEmptyString;

#[ErrorBag('login')]
final class LoginRequest implements Request
{
    use IsRequest;

    #[IsEmail]
    public string $email;

    #[IsNotEmptyString]
    public string $password;
}
