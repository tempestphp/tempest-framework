<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Http;

use PHPUnit\Framework\TestCase;
use Tempest\Http\GenericResponse;
use Tempest\Http\GenericResponseSender;
use Tempest\Http\Status;

/**
 * @internal
 * @small
 */
class GenericResponseSenderTest extends TestCase
{
    public function test_sending()
    {
        ob_start();

        $response = new GenericResponse(
            status: Status::CREATED,
            body: '{"key": "value"}',
            headers: ['Content-Type' => ['application/json']]
        );

        $responseSender = new GenericResponseSender();

        $this->assertSame($response, $responseSender->send($response));

        ob_get_clean();
    }

    public function test_sending_of_array_to_json()
    {
        ob_start();

        $response = new GenericResponse(
            status: Status::CREATED,
            body: ['key' => 'value'],
        );

        $responseSender = new GenericResponseSender();

        $response = $responseSender->send($response);

        ob_get_clean();

        $this->assertSame('{"key":"value"}', $response->getBody());
        $this->assertSame(['Content-Type' => ['application/json']], $response->getHeaders());
    }
}
