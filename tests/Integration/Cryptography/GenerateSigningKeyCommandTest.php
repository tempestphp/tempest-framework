<?php

namespace Tests\Tempest\Integration\Cryptography;

use Dotenv\Dotenv;
use Tempest\Core\FrameworkKernel;
use Tempest\Cryptography\GenerateSigningKeyCommand;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\root_path;

final class GenerateSigningKeyCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(FrameworkKernel::class)->root = __DIR__;
    }

    protected function tearDown(): void
    {
        Filesystem\delete_file(root_path('.env'));

        parent::tearDown();
    }

    public function test_creates_dot_env(): void
    {
        $this->assertFalse(Filesystem\is_file(root_path('.env')));
        $this->console->call(GenerateSigningKeyCommand::class)->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertIsString($env['SIGNING_KEY']);
    }

    public function test_updates_existing(): void
    {
        Filesystem\write_file(root_path('.env'), 'SIGNING_KEY=abc');
        $this->console->call(GenerateSigningKeyCommand::class)->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertNotSame('abc', $env['SIGNING_KEY']);
    }

    public function test_add_if_missing(): void
    {
        Filesystem\create_file(root_path('.env'));
        $this->console->call(GenerateSigningKeyCommand::class)->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertIsString($env['SIGNING_KEY']);
    }

    public function test_override_flag_true_replaces_existing_key(): void
    {
        Filesystem\write_file(root_path('.env'), 'SIGNING_KEY=original_key');
        $this->console->call(GenerateSigningKeyCommand::class, ['override' => true])->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertNotSame('original_key', $env['SIGNING_KEY']);
        $this->assertIsString($env['SIGNING_KEY']);
    }

    public function test_override_flag_false_preserves_existing_key(): void
    {
        Filesystem\write_file(root_path('.env'), 'SIGNING_KEY=original_key');
        $this->console->call(GenerateSigningKeyCommand::class, ['override' => false])->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertSame('original_key', $env['SIGNING_KEY']);
    }

    public function test_override_flag_false_still_adds_key_when_missing(): void
    {
        Filesystem\write_file(root_path('.env'), 'OTHER_VAR=value');
        $this->console->call(GenerateSigningKeyCommand::class, ['override' => false])->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertArrayHasKey('OTHER_VAR', $env);

        $this->assertSame('value', $env['OTHER_VAR']);
        $this->assertIsString($env['SIGNING_KEY']);
    }

    public function test_override_flag_preserves_other_env_variables(): void
    {
        Filesystem\write_file(root_path('.env'), "APP_NAME=Tempest\nSIGNING_KEY=old_key\nDATABASE_URL=sqlite://db.sqlite");
        $this->console->call(GenerateSigningKeyCommand::class, ['override' => true])->assertSuccess();
        $this->assertTrue(Filesystem\is_file(root_path('.env')));

        $file = Filesystem\read_file(root_path('.env'));
        $env = Dotenv::createImmutable(__DIR__)->parse($file);

        $this->assertArrayHasKey('SIGNING_KEY', $env);
        $this->assertArrayHasKey('APP_NAME', $env);
        $this->assertArrayHasKey('DATABASE_URL', $env);

        $this->assertSame('Tempest', $env['APP_NAME']);
        $this->assertSame('sqlite://db.sqlite', $env['DATABASE_URL']);

        $this->assertNotSame('old_key', $env['SIGNING_KEY']);
        $this->assertIsString($env['SIGNING_KEY']);
    }
}
