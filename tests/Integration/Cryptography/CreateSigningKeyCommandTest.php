<?php

namespace Tests\Tempest\Integration\Cryptography;

use Dotenv\Dotenv;
use Tempest\Core\FrameworkKernel;
use Tempest\Cryptography\CreateSigningKeyCommand;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\root_path;

final class CreateSigningKeyCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(FrameworkKernel::class)->root = __DIR__;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Filesystem\delete_file(root_path('.env'));
    }

    public function test_creates_dot_env(): void
    {
        $this->assertFalse(Filesystem\is_file(root_path('.env')));
        $this->console->call(CreateSigningKeyCommand::class)->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertIsString($env['SIGNING_KEY']);
    }

    public function test_updates_existing(): void
    {
        Filesystem\write_file(root_path('.env'), 'SIGNING_KEY=abc');
        $this->console->call(CreateSigningKeyCommand::class)->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertNotSame('abc', $env['SIGNING_KEY']);
    }

    public function test_add_if_missing(): void
    {
        Filesystem\create_file(root_path('.env'));
        $this->console->call(CreateSigningKeyCommand::class)->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertIsString($env['SIGNING_KEY']);
    }
}
