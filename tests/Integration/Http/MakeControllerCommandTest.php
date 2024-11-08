<?php

namespace Tests\Tempest\Integration\Http;

use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MakeControllerCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new ComposerNamespace('App\\', __DIR__ . '/install/App')
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    public function test_make_with_defaults(): void
    {
        $this->console
            ->call('make:controller BookController')
            ->submit();

        $this->installer
            ->assertFileExists('App/BookController.php')
            ->assertFileContains('App/BookController.php', 'namespace App;');
    }

    public function test_make_with_other_namespace(): void
    {
        $this->console
            ->call('make:controller Books\\BookController')
            ->submit();
        
        $this->installer
            ->assertFileExists('App/Books/BookController.php')
            ->assertFileContains('App/Books/BookController.php', 'namespace App\\Books;');
    }

    public function test_make_with_input_path(): void
    {
        $this->console
            ->call('make:controller Books/BookController')
            ->submit();
        
        $this->installer
            ->assertFileExists('App/Books/BookController.php')
            ->assertFileContains('App/Books/BookController.php', 'namespace App\\Books;');
    }
}