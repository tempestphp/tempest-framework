<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Http\Session;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tempest\Clock\MockClock;
use Tempest\Http\Session\ArraySessionHandler;
use Tempest\Http\Session\Session;

/**
 * @internal
 * @small
 */
class SessionTest extends TestCase
{
    private Session $session;

    public function test_starting_session()
    {
        $this->assertFalse($this->session->isStarted());

        $this->session->start();

        $this->assertTrue($this->session->isStarted());
    }

    public function test_exception_is_thrown_if_session_was_started_by_php()
    {
        $this->expectExceptionObject(
            new RuntimeException('The session has already been started by PHP.')
        );

        session_start();

        $this->session->start();
    }

    public function test_getting_and_setting_session_keys()
    {
        $this->assertFalse($this->session->has('test-key-1'));
        $this->assertTrue($this->session->missing('test-key-1'));

        $this->session->set('test-key-1', 'test-value-1');

        $this->assertTrue($this->session->has('test-key-1'));
        $this->assertFalse($this->session->missing('test-key-1'));
        $this->assertSame('test-value-1', $this->session->get('test-key-1'));
    }

    public function test_getting_and_setting_session_keys_with_dot_notation()
    {
        $this->assertFalse($this->session->has('user.id'));

        $this->session->set('user.id', 1);

        $this->assertTrue($this->session->has('user.id'));
        $this->assertEqualsCanonicalizing(
            ['user' => ['id' => 1]],
            $this->session->all()
        );
    }

    public function test_getting_all_session_values()
    {
        $this->session->start();

        $this->session->set('test-key-1', 'test-value-1');
        $this->session->set('test-key-2', 'test-value-2');
        $this->session->set('test-key-3', 'test-value-3');

        $this->assertEqualsCanonicalizing(
            [
                'test-key-1' => 'test-value-1',
                'test-key-2' => 'test-value-2',
                'test-key-3' => 'test-value-3',
            ],
            $this->session->all()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        session_abort();

        $this->session = new Session(
            new ArraySessionHandler(new MockClock())
        );

        $_SESSION = [];
    }
}
