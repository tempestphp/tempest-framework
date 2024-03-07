<?php

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionManager;
use Tempest\Http\Session\SessionConfig;
use Tempest\Testing\IntegrationTest;

final class FileSessionTest extends IntegrationTest
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . '/sessions';

        $this->container->config(new SessionConfig(path: $this->path));
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("{$this->path}/*"));
        rmdir($this->path);
    }

    /** @test */
    public function create_session_from_container()
    {
        $session = $this->container->get(SessionManager::class);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertTrue($session->isValid());
    }

    /** @test */
    public function test_put_get()
    {
        $session = $this->container->get(SessionManager::class);

        $session->put('test', 'value');
        $value = $session->get('test');
        $this->assertEquals('value', $value);
    }

    /** @test */
    public function test_remove()
    {
        $session = $this->container->get(SessionManager::class);

        $session->put('test', 'value');
        $session->remove('test');
        $value = $session->get('test');
        $this->assertNull($value);
    }
}