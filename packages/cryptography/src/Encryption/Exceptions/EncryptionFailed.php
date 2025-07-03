<?php

namespace Tempest\Cryptography\Encryption\Exceptions;

use Exception;
use Tempest\Core\HasContext;

final class EncryptionFailed extends Exception implements EncryptionException, HasContext
{
    public function __construct(
        string $message,
        private readonly array $context = [],
    ) {
        parent::__construct($message);
    }

    public static function becauseOpenSslFailed(string $error): self
    {
        return new self('OpenSSL encryption failed.', ['error' => $error]);
    }

    public function context(): array
    {
        return $this->context;
    }
}
