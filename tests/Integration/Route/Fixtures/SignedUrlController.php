<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\ValidSignature;

final readonly class SignedUrlController
{
    #[Get('/signed-action/{token}')]
    #[ValidSignature]
    public function signedAction(string $token): Response
    {
        return new Ok(['token' => $token, 'message' => 'Signature valid']);
    }

    #[Get('/unsigned-action/{token}')]
    public function unsignedAction(string $token): Response
    {
        return new Ok(['token' => $token]);
    }
}
