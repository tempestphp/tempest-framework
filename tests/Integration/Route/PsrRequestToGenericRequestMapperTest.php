<?php

declare(strict_types=1);

namespace Integration\Route;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Laminas\Diactoros\Uri;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Request;
use Tempest\Http\Upload;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class PsrRequestToGenericRequestMapperTest extends FrameworkIntegrationTestCase
{
    private Encrypter $encrypter {
        get => $this->container->get(Encrypter::class);
    }

    private CookieManager $cookies {
        get => $this->container->get(CookieManager::class);
    }

    private PsrRequestToGenericRequestMapper $mapper {
        get => new PsrRequestToGenericRequestMapper($this->encrypter, $this->cookies);
    }

    public function test_generic_request_is_used_when_interface_is_passed(): void
    {
        $request = $this->mapper->map(
            from: $this->http->makePsrRequest('/'),
            to: Request::class,
        );

        $this->assertInstanceOf(GenericRequest::class, $request);
    }

    public function test_raw(): void
    {
        $stream = new Stream(fopen('php://memory', 'r+'));
        $stream->write(json_encode(['foo' => 'bar']));
        $stream->rewind();

        $_COOKIE['test'] = $this->encrypter->encrypt('cookie-value')->serialize();

        $request = $this->mapper->map(new ServerRequest(
            uri: new Uri('/json-endpoint'),
            method: 'POST',
            body: $stream,
            headers: [
                'Content-Type' => 'application/json',
            ],
        ), to: Request::class);

        $this->assertEquals(json_encode(['foo' => 'bar']), $request->raw);
        $this->assertEquals(['foo' => 'bar'], $request->body);
        $this->assertEquals('cookie-value', $request->getCookie('test')?->value);
    }

    public function test_files(): void
    {
        $currentPath = __DIR__ . '/Fixtures/upload-current.txt';

        copy(__DIR__ . '/Fixtures/upload.txt', $currentPath);

        /** @var GenericRequest $request */
        $request = $this->mapper->map(
            from: $this->http->makePsrRequest('/', files: [new UploadedFile(
                streamOrFile: $currentPath,
                size: 1,
                errorStatus: UPLOAD_ERR_OK,
                clientFilename: 'hello',
                clientMediaType: 'application/octet-stream',
            )]),
            to: Request::class,
        );

        $this->assertCount(1, $request->files);
        $this->assertInstanceOf(Upload::class, $request->files[0]);

        $upload = $request->files[0];

        $this->assertSame('hello', $upload->getStream()->getContents());
        $movePath = __DIR__ . '/Fixtures/upload-moved.txt';
        $upload->moveTo($movePath);

        $this->assertFalse(file_exists($currentPath));
        $this->assertTrue(file_exists($movePath));

        $this->assertSame(1, $upload->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $upload->getError());
        $this->assertSame('hello', $upload->getClientFilename());
        $this->assertSame('application/octet-stream', $upload->getClientMediaType());
    }

    public function test_body_field_in_body(): void
    {
        $request = $this->mapper->map(
            from: $this->http->makePsrRequest(
                uri: '/',
                body: [
                    'body' => 'text',
                ],
            ),
            to: GenericRequest::class,
        );

        $this->assertSame(['body' => 'text'], $request->body);
    }

    public function test_unencrypted_cookies_are_discarded(): void
    {
        $request = $this->mapper->map(
            from: $this->http->makePsrRequest(
                uri: '/',
                body: [
                    'body' => 'text',
                ],
                cookies: [
                    'foo' => 'bar',
                ],
            ),
            to: GenericRequest::class,
        );

        $this->assertSame([], $request->cookies);

        $cookies = $this->cookies->all();
        $this->assertSame($cookies['foo']->expiresAt, -1);
        $this->assertSame($cookies['foo']->value, '');
    }
}
