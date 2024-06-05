<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use Tempest\Auth\Authenticator;
use Tempest\Auth\DatabaseAuthenticationCall;
use Tempest\Auth\Exceptions\InvalidLoginException;
use Tempest\Auth\IdentifierResolver;
use Tempest\Container\Tag;
use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Http\Request;
use Tempest\Http\Response;
use function Tempest\response;

final readonly class AuthController
{
    public function __construct(
        #[Tag('database')]
        private IdentifierResolver $identifierResolver,
        private Authenticator $authenticator,
    ) {
    }

    /**
     * @throws InvalidLoginException
     */
    #[Post('/login')]
    public function login(Request $request): Response
    {
        $user = $this->identifierResolver->resolve(new DatabaseAuthenticationCall(
            identifier: $request->get('email'),
            password: $request->get('password')
        ));

        $this->authenticator->login($user);

        return response()->ok();
    }

    #[Post('/logout')]
    public function logout(): Response
    {
        $this->authenticator->logout();

        return response()->ok();
    }

    #[Get('/me')]
    public function user(): Response
    {
        return response()
            ->ok()
            ->addHeader('Content-Type', 'application/json')
            ->setBody(json_encode($this->identifierResolver->getIdentifiable()));
    }
}
