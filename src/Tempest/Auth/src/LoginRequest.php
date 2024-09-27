<?php

namespace Tempest\Auth;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rules\Email;

final class LoginRequest implements Request
{
    use IsRequest;

    #[Email]
    public string $email;

    public string $password;

    private User $user;

    public function validate(): void
    {
        $user = User::query()
            ->where('email = :email', email: $this->email)
            ->where('password = :password', password: $this->getEncryptedPassword())
            ->first();

        if (! $user) {
            throw new ValidationException($this, [
                'email' => [new UnknownUserRule()]
            ]);
        }

        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    private function getEncryptedPassword(): string
    {
        return password_hash($this->password, PASSWORD_BCRYPT);
    }
}