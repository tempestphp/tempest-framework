<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use JsonSerializable;
use Stringable;
use Tempest\DateTime\Duration;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Responses\Download;
use Tempest\Http\Responses\EventStream;
use Tempest\Http\Responses\File;
use Tempest\Http\Responses\Ok;
use Tempest\Http\ServerSentEvent;
use Tempest\Http\ServerSentMessage;
use Tempest\Http\Status;
use Tempest\Router\GenericResponseSender;
use Tempest\View\ViewRenderer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\View\view;

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

    public function test_sending_head_request(): void
    {
        $request = new GenericRequest(
            method: Method::HEAD,
            uri: '/test',
        );

        $this->container->singleton(Request::class, $request);

        $responseSender = new GenericResponseSender(
            $this->container,
            $this->container->get(ViewRenderer::class),
        );

        $response = new GenericResponse(
            status: Status::OK,
            body: 'body',
            headers: ['x-custom' => ['true']],
        );

        ob_start();

        $responseSender->send($response);

        $content = ob_get_clean();

        $this->assertSame('', $content);
    }

    public function test_file_response(): void
    {
        ob_start();
        $path = __DIR__ . '/Fixtures/sample.png';
        $response = new File(path: $path);
        $responseSender = $this->container->get(GenericResponseSender::class);
        $responseSender->send($response);

        $content = ob_get_clean();

        $this->assertSame(file_get_contents($path), $content);
    }

    public function test_download_response(): void
    {
        ob_start();
        $path = __DIR__ . '/Fixtures/sample.png';
        $response = new Download(path: $path);
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

    public function test_sending_of_json_serializable_to_json(): void
    {
        ob_start();
        $response = new GenericResponse(
            status: Status::CREATED,
            body: new class implements JsonSerializable {
                public function jsonSerialize(): mixed
                {
                    return ['key' => 'value'];
                }
            },
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

    public function test_stream(): void
    {
        ob_start();
        $response = new EventStream(fn () => yield 'hello');
        $responseSender = $this->container->get(GenericResponseSender::class);
        $responseSender->send($response);

        $output = ob_get_clean();
        ob_start();

        $this->assertStringContainsString('event: message', $output);
        $this->assertStringContainsString('data: "hello"', $output);
    }

    public function test_stream_with_custom_event(): void
    {
        ob_start();
        $response = new EventStream(function () {
            yield new ServerSentMessage(data: 'hello', event: 'first');
            yield new ServerSentMessage(data: 'goodbye', event: 'last');
        });
        $responseSender = $this->container->get(GenericResponseSender::class);
        $responseSender->send($response);

        $output = ob_get_clean();
        ob_start();

        $this->assertStringContainsString('event: first', $output);
        $this->assertStringContainsString('data: "hello"', $output);
        $this->assertStringContainsString('event: last', $output);
        $this->assertStringContainsString('data: "goodbye"', $output);
    }

    public function test_stream_with_custom_id(): void
    {
        ob_start();
        $response = new EventStream(function () {
            yield new ServerSentMessage(data: 'hello', id: 123);
            yield new ServerSentMessage(data: 'goodbye', id: 456);
        });
        $responseSender = $this->container->get(GenericResponseSender::class);
        $responseSender->send($response);

        $output = ob_get_clean();
        ob_start();

        $this->assertStringContainsString('id: 123', $output);
        $this->assertStringContainsString('data: "hello"', $output);
        $this->assertStringContainsString('id: 456', $output);
        $this->assertStringContainsString('data: "goodbye"', $output);
    }

    public function test_stream_with_custom_retry(): void
    {
        ob_start();
        $response = new EventStream(function () {
            yield new ServerSentMessage(data: 'hello', retryAfter: 1000);
            yield new ServerSentMessage(data: 'goodbye', retryAfter: Duration::minute());
        });
        $responseSender = $this->container->get(GenericResponseSender::class);
        $responseSender->send($response);

        $output = ob_get_clean();
        ob_start();

        $this->assertStringContainsString('retry: 1000', $output);
        $this->assertStringContainsString('data: "hello"', $output);
        $this->assertStringContainsString('retry: 60000', $output);
        $this->assertStringContainsString('data: "goodbye"', $output);
    }

    public function test_stream_with_custom_implementation(): void
    {
        ob_start();
        $response = new EventStream(function () {
            yield new class implements ServerSentEvent {
                public ?int $id = 1;

                public null|Duration|int $retryAfter = null;

                public ?string $event = 'custom';

                public JsonSerializable|Stringable|string|iterable $data = ['foo', 'bar'];
            };
        });
        $responseSender = $this->container->get(GenericResponseSender::class);
        $responseSender->send($response);

        $output = ob_get_clean();
        ob_start();

        $this->assertStringContainsString('id: 1', $output);
        $this->assertStringNotContainsString('retry:', $output);
        $this->assertStringContainsString('event: custom', $output);
        $this->assertStringContainsString('data: foo', $output);
        $this->assertStringContainsString('data: bar', $output);
    }
}
