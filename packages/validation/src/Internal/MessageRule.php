<?php

declare(strict_types=1);

namespace Tempest\Validation\Internal;

use Tempest\Validation\HasErrorMessage;
use Tempest\Validation\Rule;

/** @internal */
final readonly class MessageRule implements Rule, HasErrorMessage
{
    public function __construct(
        private string $message,
    ) {}

    public function isValid(mixed $value): bool
    {
        return false;
    }

    public function getErrorMessage(): string
    {
        return $this->message;
    }
}
