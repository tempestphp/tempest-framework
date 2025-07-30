<?php

namespace Tests\Tempest\Integration\Cryptography;

use Tempest\Cryptography\Password\BcryptConfig;
use Tempest\Cryptography\Password\HashingAlgorithm;
use Tempest\Cryptography\Password\PasswordHasher;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class PasswordHasherTest extends FrameworkIntegrationTestCase
{
    public function test_default_algorithm(): void
    {
        $hasher = $this->container->get(PasswordHasher::class);
        $this->assertSame(HashingAlgorithm::ARGON2ID, $hasher->algorithm);
    }

    public function test_hash_verify(): void
    {
        $hasher = $this->container->get(PasswordHasher::class);

        $password = 'my_secure_password'; // @mago-expect security/no-literal-password
        $hash = $hasher->hash($password);

        $this->assertTrue($hasher->verify($password, $hash));
    }

    public function test_update_config(): void
    {
        $this->container->config(new BcryptConfig());

        $hasher = $this->container->get(PasswordHasher::class);
        $this->assertSame(HashingAlgorithm::BCRYPT, $hasher->algorithm);
    }

    public function needs_rehash(): void
    {
        $this->container->config(new BcryptConfig(cost: 2));
        $hasher1 = $this->container->get(PasswordHasher::class);
        $hash = $hasher1->hash('my_secure_password');

        $this->container->config(new BcryptConfig(cost: 3));
        $hasher2 = $this->container->get(PasswordHasher::class);

        $this->assertFalse($hasher1->needsRehash($hash));
        $this->assertTrue($hasher2->needsRehash($hash));
    }
}
