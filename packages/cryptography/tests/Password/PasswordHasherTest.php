<?php

namespace Tempest\Cryptography\Tests\Password;

use PHPUnit\Framework\TestCase;
use Tempest\Cryptography\Password\ArgonConfig;
use Tempest\Cryptography\Password\BcryptConfig;
use Tempest\Cryptography\Password\Exceptions\HashingFailed;
use Tempest\Cryptography\Password\GenericPasswordHasher;
use Tempest\Cryptography\Password\HashingAlgorithm;

final class PasswordHasherTest extends TestCase
{
    public function test_algorithm(): void
    {
        $hasher = new GenericPasswordHasher(new ArgonConfig());
        $this->assertSame(HashingAlgorithm::ARGON2ID, $hasher->algorithm);

        $hasher = new GenericPasswordHasher(new BcryptConfig());
        $this->assertSame(HashingAlgorithm::BCRYPT, $hasher->algorithm);
    }

    public function test_config_options(): void
    {
        $this->assertSame(
            ['memory_cost' => 1024, 'time_cost' => 2, 'threads' => 2],
            new ArgonConfig(memoryCost: 1024, timeCost: 2, threads: 2)->options,
        );

        $this->assertSame(
            ['cost' => 10],
            (new BcryptConfig(cost: 10))->options,
        );
    }

    public function test_hash_verify(): void
    {
        $hasher = new GenericPasswordHasher(new ArgonConfig());
        $password = 'my_secure_password'; // @mago-expect security/no-literal-password
        $hash = $hasher->hash($password);

        $this->assertTrue($hasher->verify($password, $hash));
    }

    public function test_wrong_password(): void
    {
        $hasher = new GenericPasswordHasher(new ArgonConfig());
        $hash = $hasher->hash('my_secure_password');

        $this->assertFalse($hasher->verify('wrong_password', $hash));
    }

    public function test_needs_rehash(): void
    {
        $hasher1 = new GenericPasswordHasher(new ArgonConfig(timeCost: 2));
        $hasher2 = new GenericPasswordHasher(new ArgonConfig(timeCost: 4));
        $hash = $hasher1->hash('my_secure_password');

        $this->assertFalse($hasher1->needsRehash($hash));
        $this->assertTrue($hasher2->needsRehash($hash));
    }

    public function test_analyze(): void
    {
        $hasher = new GenericPasswordHasher(new ArgonConfig(
            memoryCost: 1024,
            timeCost: 2,
            threads: 2,
        ));

        $hash = $hasher->hash('my_secure_password');
        $analysis = $hasher->analyze($hash);

        $this->assertSame($hash, $analysis->hash);
        $this->assertSame(HashingAlgorithm::ARGON2ID, $analysis->algorithm);
        $this->assertInstanceOf(ArgonConfig::class, $analysis->config);
        $this->assertSame(1024, $analysis->config->memoryCost);
        $this->assertSame(2, $analysis->config->timeCost);
        $this->assertSame(2, $analysis->config->threads);
    }

    public function test_hashing_failed_empty_password(): void
    {
        $this->expectException(HashingFailed::class);
        $this->expectExceptionMessage('Could not hash an empty password.');

        $hasher = new GenericPasswordHasher(new BcryptConfig());
        $hasher->hash('');
    }
}
