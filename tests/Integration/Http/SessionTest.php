<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Session\Session;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SessionTest extends FrameworkIntegrationTestCase
{
    private Session $session {
        get => $this->container->get(Session::class);
    }

    #[Test]
    public function create_session_from_container(): void
    {
        $clock = $this->clock();

        $this->assertInstanceOf(Session::class, $this->session);
        $this->assertTrue($this->session->createdAt->equals($clock->now()));
        $this->assertTrue($this->session->lastActiveAt->equals($clock->now()));
    }

    #[Test]
    public function set_and_get(): void
    {
        $this->session->set('test', 'value');
        $this->assertEquals('value', $this->session->get('test'));

        $this->session->set('nested', ['key' => 'value']);
        $this->assertEquals(['key' => 'value'], $this->session->get('nested'));
    }

    #[Test]
    public function get_with_default(): void
    {
        $this->assertEquals('default', $this->session->get('nonexistent', 'default'));
        $this->assertNull($this->session->get('nonexistent'));
    }

    #[Test]
    public function remove(): void
    {
        $this->session->set('test', 'value');
        $this->assertEquals('value', $this->session->get('test'));

        $this->session->remove('test');
        $this->assertNull($this->session->get('test'));
    }

    #[Test]
    public function all(): void
    {
        $this->session->set('key1', 'value1');
        $this->session->set('key2', 'value2');

        $data = $this->session->all();

        $this->assertArrayHasKey('key1', $data);
        $this->assertArrayHasKey('key2', $data);
        $this->assertEquals('value1', $data['key1']);
        $this->assertEquals('value2', $data['key2']);
    }

    #[Test]
    public function flash(): void
    {
        $this->session->flash('message', 'success');
        $this->assertEquals('success', $this->session->get('message'));

        $this->session->cleanup();
        $this->assertNull($this->session->get('message'));
    }

    #[Test]
    public function reflash(): void
    {
        $this->session->flash('test', 'value');
        $this->session->flash('test2', ['key' => 'value']);

        $this->assertEquals('value', $this->session->get('test'));

        $this->session->reflash();
        $this->session->cleanup();

        $this->assertEquals('value', $this->session->get('test'));
        $this->assertEquals(['key' => 'value'], $this->session->get('test2'));
    }

    #[Test]
    public function consume(): void
    {
        $this->session->set('token', 'abc123');

        $this->assertEquals('abc123', $this->session->consume('token'));
        $this->assertNull($this->session->get('token'));
    }

    #[Test]
    public function consume_with_default(): void
    {
        $this->assertEquals('default', $this->session->consume('nonexistent', 'default'));
    }

    #[Test]
    public function clear(): void
    {
        $this->session->set('key1', 'value1');
        $this->session->set('key2', 'value2');

        $this->assertCount(2, $this->session->all());

        $this->session->clear();

        $this->assertEmpty($this->session->all());
    }
}
