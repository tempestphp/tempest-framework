<?php

namespace Tempest\Cryptography\Encryption;

use Tempest\Cryptography\Encryption\Exceptions\AlgorithmMismatched;
use Tempest\Cryptography\Encryption\Exceptions\DecryptionFailed;
use Tempest\Cryptography\Encryption\Exceptions\EncryptionFailed;
use Tempest\Cryptography\Encryption\Exceptions\SignatureMismatched;
use Tempest\Cryptography\Signing\Signer;

final class GenericEncrypter implements Encrypter
{
    public EncryptionAlgorithm $algorithm {
        get => $this->config->algorithm;
    }

    public EncryptionKey $key {
        get => EncryptionKey::fromString($this->config->key, $this->algorithm);
    }

    public function __construct(
        private readonly Signer $signer,
        private readonly EncryptionConfig $config,
    ) {}

    public function encrypt(#[\SensitiveParameter] string $data): EncryptedData
    {
        $iv = random_bytes($this->algorithm->getIvLength());
        $tag = '';

        if ($this->algorithm->isAead()) {
            $payload = openssl_encrypt(
                data: $data,
                cipher_algo: $this->algorithm->value,
                passphrase: $this->key->value,
                options: OPENSSL_RAW_DATA,
                iv: $iv,
                tag: $tag,
            );
        } else {
            $payload = openssl_encrypt(
                data: $data,
                cipher_algo: $this->algorithm->value,
                passphrase: $this->key->value,
                options: OPENSSL_RAW_DATA,
                iv: $iv,
            );
        }

        if ($payload === false) {
            throw EncryptionFailed::becauseOpenSslFailed(openssl_error_string());
        }

        $signature = $this->signer->sign(implode('.', array_filter([
            $this->algorithm->value,
            base64_encode($iv),
            base64_encode($payload),
            $this->algorithm->isAead() ? base64_encode($tag) : null,
        ])));

        return new EncryptedData(
            payload: $payload,
            iv: $iv,
            tag: $tag,
            signature: $signature,
            algorithm: $this->algorithm,
        );
    }

    public function decrypt(string|EncryptedData $data): string
    {
        if (is_string($data)) {
            $data = EncryptedData::unserialize($data);
        }

        $signature = implode('.', array_filter([
            $data->algorithm->value,
            base64_encode($data->iv),
            base64_encode($data->payload),
            $data->algorithm->isAead() ? base64_encode($data->tag) : null,
        ]));

        if (! $this->signer->verify($signature, $data->signature)) {
            throw SignatureMismatched::raise();
        }

        if ($data->algorithm !== $this->algorithm) {
            throw AlgorithmMismatched::betweenKeyAndData();
        }

        if ($data->algorithm->isAead()) {
            $decrypted = openssl_decrypt(
                data: $data->payload,
                cipher_algo: $data->algorithm->value,
                passphrase: $this->key->value,
                options: OPENSSL_RAW_DATA,
                iv: $data->iv,
                tag: $data->tag,
            );
        } else {
            $decrypted = openssl_decrypt(
                data: $data->payload,
                cipher_algo: $data->algorithm->value,
                passphrase: $this->key->value,
                options: OPENSSL_RAW_DATA,
                iv: $data->iv,
            );
        }

        if ($decrypted === false) {
            throw DecryptionFailed::becauseOpenSslFailed(openssl_error_string());
        }

        return $decrypted;
    }
}
