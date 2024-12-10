<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Status;
use Tempest\Router\GenericResponse;
use Tempest\Router\GenericResponseSender;
use Tempest\Router\Responses\Download;
use Tempest\Router\Responses\File;
use Tempest\Router\Responses\Ok;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\view;

/**
 * @internal
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

    public function test_file_response(): void
    {
        ob_start();

        $path = __DIR__ . '/Fixtures/sample.png';

        $response = new File(
            path: $path,
        );

        $responseSender = $this->container->get(GenericResponseSender::class);

        $responseSender->send($response);

        $content = ob_get_clean();

        $this->assertSame(file_get_contents($path), $content);
    }

    public function test_download_response(): void
    {
        ob_start();

        $path = __DIR__ . '/Fixtures/sample.png';

        $response = new Download(
            path: $path,
        );

        $responseSender = $this->container->get(GenericResponseSender::class);

        $responseSender->send($response);

        $content = ob_get_clean();

        $this->assertSame(file_get_contents($path), $content);
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
            body: view(__DIR__ . '/../../Fixtures/Views/overview.view.php')->data(
                name: 'Brent',
            ),
        );

        $responseSender = $this->container->get(GenericResponseSender::class);

        $responseSender->send($response);

        $output = ob_get_clean();

        $this->assertStringContainsString('Hello Brent!', $output);
    }
}
