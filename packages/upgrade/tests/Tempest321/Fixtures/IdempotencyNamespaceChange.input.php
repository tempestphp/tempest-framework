<?php

use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\Attributes\IdempotentRoute;
use Tempest\Idempotency\Exceptions\IdempotencyKeyWasAlreadyUsed;
use Tempest\Router\Post;

final class OrderController
{
    #[Post('/orders')]
    #[Idempotent]
    #[IdempotentRoute(requireKey: true)]
    public function create(): void
    {
        throw new IdempotencyKeyWasAlreadyUsed();
    }
}
