<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Http\Session;

use PHPUnit\Framework\TestCase;
use Tempest\Clock\MockClock;
use Tempest\Http\Session\ArraySessionHandler;

class ArraySessionHandlerTest extends TestCase
{
    private MockClock $clock;

    private ArraySessionHandler $sessionHandler;

    public function test_open_and_close()
    {
        $this->assertTrue($this->sessionHandler->open('test-path', 'test-name'));
        $this->assertTrue($this->sessionHandler->close());
    }

    public function test_opening_saved_session()
    {
        $this->sessionHandler->open('test-path', 'test-name');
        $this->sessionHandler->write(
            'blah-id',
            serialize(['test' => 'value'])
        );
        $this->sessionHandler->close();
        $this->sessionHandler->open('test-path', 'test-name');

        $value = unserialize(
            $this->sessionHandler->read('blah-id')
        );

        $this->assertEqualsCanonicalizing(
            ['test' => 'value'],
            $value
        );
    }

    public function test_opening_expired_session()
    {
        $this->sessionHandler->open('test-path', 'test-name');
        $this->sessionHandler->write(
            'blah-id',
            serialize(['test' => 'value'])
        );
        $this->sessionHandler->close();
        $this->sessionHandler->open('test-path', 'test-name');

        $this->clock->sleep(3601);

        $this->assertSame('', $this->sessionHandler->read('blah-id'));
    }

    public function test_garbage_collection()
    {
        $this->sessionHandler->open('test-path', 'test-name');
        $this->sessionHandler->write(
            'foo-bar',
            serialize(['test' => 'value'])
        );

        $this->clock->sleep(3601);

        $this->sessionHandler->gc(3600);

        $this->assertSame('', $this->sessionHandler->read('foo-bar'));
    }

    public function test_destroying_a_session()
    {
        $this->sessionHandler->open('test-path', 'test-name');
        $this->sessionHandler->write(
            'fooly-bar',
            serialize(['test' => 'value'])
        );

        $this->assertNotSame('', $this->sessionHandler->read('fooly-bar'));
        $this->sessionHandler->destroy('fooly-bar');
        $this->assertSame('', $this->sessionHandler->read('fooly-bar'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionHandler = new ArraySessionHandler(
            $this->clock = new MockClock()
        );
    }
}
