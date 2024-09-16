<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Status;
use Tempest\Router\GenericResponse;
use Tempest\Router\GenericResponseSender;
use Tempest\Router\Responses\Ok;
use function Tempest\view;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class GenericResponseSenderTest extends FrameworkIntegrationTestCase
{
    public function test_sending(): void
    {
        ob_start();

        $response = new GenericResponse(
            status: Status::CREATED,
            body: '{"key": "value"}',
            headers: ['Content-Type' => ['application/json']],
        );

        $responseSender = $this->container->get(GenericResponseSender::class);

        $this->assertSame($response, $responseSender->send($response));

        ob_get_clean();
    }

    public function test_sending_of_array_to_json(): void
    {
        ob_start();

        $response = new GenericResponse(
            status: Status::CREATED,
            body: ['key' => 'value'],
        );

        $responseSender = $this->container->get(GenericResponseSender::class);

        $responseSender->send($response);

        $output = ob_get_clean();

        $this->assertSame('{"key":"value"}', $output);
    }

    public function test_view_body(): void
    {
        ob_start();

        $response = new Ok(
            body: view('Views/overview.view.php')->data(
                name: 'Brent',
            ),
        );

        $responseSender = $this->container->get(GenericResponseSender::class);

        $responseSender->send($response);

        $output = ob_get_clean();

        $this->assertStringContainsString('Hello Brent!', $output);
    }
}
