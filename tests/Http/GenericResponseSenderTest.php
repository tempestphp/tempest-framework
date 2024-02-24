<?php

declare(strict_types=1);

namespace Tests\Tempest\Http;

use PHPUnit\Framework\TestCase;
use Tempest\Http\GenericResponse;
use Tempest\Http\GenericResponseSender;
use Tempest\Http\Status;

class GenericResponseSenderTest extends TestCase
{
    public function test_sending()
    {
        ob_start();

        $response = new GenericResponse(
            status: Status::CREATED,
            body: '{"key": "value"}',
            headers: ['Content-Type' => 'application/json']
        );

        $responseSender = new GenericResponseSender();

        $this->assertSame($response, $responseSender->send($response));

        ob_get_clean();
    }
}
