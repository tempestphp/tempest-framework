<?php

namespace Tempest\Cryptography\Encryption;

use Stringable;
use Tempest\Cryptography\Encryption\Exceptions\EncryptedDataWasInvalid;
use Tempest\Cryptography\Signing\Signature;
use Tempest\Support\Json;

final readonly class EncryptedData implements Stringable
{
    public function __construct(
        private(set) string $payload,
        private(set) string $iv,
        private(set) string $tag,
        private(set) Signature $signature,
        private(set) EncryptionAlgorithm $algorithm,
    ) {}

    public function serialize(): string
    {
        $data = [
            'payload' => base64_encode($this->payload),
            'iv' => base64_encode($this->iv),
            'tag' => base64_encode($this->tag),
            'signature' => $this->signature->value,
            'algorithm' => $this->algorithm->value,
        ];

        return base64_encode(Json\encode($data));
    }

    public static function unserialize(string $data): self
    {
        $decoded = Json\decode(base64_decode($data, strict: true));

        if (! is_array($decoded) || ! isset($decoded['payload'], $decoded['iv'], $decoded['tag'], $decoded['signature'], $decoded['algorithm'])) {
            throw EncryptedDataWasInvalid::dueToInvalidFormat();
        }

        return new self(
            payload: base64_decode($decoded['payload'], strict: true),
            iv: base64_decode($decoded['iv'], strict: true),
            tag: base64_decode($decoded['tag'], strict: true),
            signature: new Signature($decoded['signature']),
            algorithm: EncryptionAlgorithm::from($decoded['algorithm']),
        );
    }

    public function __toString(): string
    {
        return $this->serialize();
    }
}
