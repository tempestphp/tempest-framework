<?php

namespace Tests\Tempest\Http;

use PHPUnit\Framework\TestCase;
use Tempest\Http\GenericResponse;
use Tempest\Http\GenericResponseSender;
use Tempest\Http\Status;

class GenericResponseSenderTest extends TestCase
{
    public function test_sending()
    {
        $response = new GenericResponse(
            status: Status::CREATED,
            body: '{"key": "value"}',
            headers: ['Content-Type' => 'application/json']
        );

        $responseSender = new GenericResponseSender();

        $this->assertSame($response, $responseSender->send($response));
    }
}