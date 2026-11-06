<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use ArrayObject;
use PHPUnit\Framework\Attributes\Test;
use Tempest\DateTime\DateTime;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionId;
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

        // Start of the next request: the value is still available (e.g. to render).
        $this->session->cleanup();
        $this->assertEquals('success', $this->session->get('message'));

        // Start of the request after that: it has expired.
        $this->session->cleanup();
        $this->assertNull($this->session->get('message'));
    }

    #[Test]
    public function flash_value_expires_even_when_never_read(): void
    {
        $this->session->flash('message', 'success');

        // Aging is independent of reads: two request boundaries expire the value
        // without it ever being retrieved.
        $this->session->cleanup();
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

    #[Test]
    public function replace_id_preserves_data(): void
    {
        $this->session->set('key', 'value');

        $this->session->replaceId(new SessionId('new_session'));

        $this->assertSame('new_session', (string) $this->session->id);
        $this->assertSame('value', $this->session->get('key'));
    }

    #[Test]
    public function session_is_reset(): void
    {
        $originalSession = $this->container->get(Session::class);

        $this->container->reset();

        $newSession = $this->container->get(Session::class);

        $this->assertNotSame($originalSession, $newSession);
    }

    #[Test]
    public function is_dirty_after_changes(): void
    {
        $session = $this->createSession(['user' => 'brent']);
        $this->assertFalse($session->isDirty());

        $session->set('user', 'brent');
        $this->assertFalse($session->isDirty());

        $session->set('user', 'aidan');
        $this->assertTrue($session->isDirty());

        $session->markAsPersisted();
        $this->assertFalse($session->isDirty());

        $session->remove('user');
        $this->assertTrue($session->isDirty());
    }

    #[Test]
    public function is_dirty_after_values_are_modified_in_place(): void
    {
        $session = $this->createSession(['cart' => new ArrayObject()]);

        $session->get('cart')->append('book');

        $this->assertTrue($session->isDirty());
    }

    #[Test]
    public function is_dirty_after_flash_values_are_aged(): void
    {
        $session = $this->createSession();
        $session->flash('success', 'Saved!');
        $session->markAsPersisted();

        $session->cleanup();

        $this->assertTrue($session->isDirty());
    }

    #[Test]
    public function is_not_dirty_after_being_unserialized(): void
    {
        $session = $this->createSession();
        $session->set('user', 'brent');

        $this->assertFalse(unserialize(serialize($session))->isDirty());
    }

    private function createSession(array $data = []): Session
    {
        $now = DateTime::now();

        return new Session(
            id: new SessionId('dirty-test'),
            createdAt: $now,
            lastActiveAt: $now,
            data: $data,
        );
    }
}
