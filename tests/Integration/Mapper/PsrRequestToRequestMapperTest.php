<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Router\GenericRequest;
use Tempest\Router\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Router\Request;
use Tempest\Router\Upload;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class PsrRequestToRequestMapperTest extends FrameworkIntegrationTestCase
{
    public function test_generic_request_is_used_when_interface_is_passed(): void
    {
        $mapper = new PsrRequestToGenericRequestMapper();

        $request = $mapper->map(
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

        $request = new PsrRequestToGenericRequestMapper()->map(new ServerRequest(
            uri: new Uri('/json-endpoint'),
            method: 'POST',
            body: $stream,
            headers: [
                'Content-Type' => 'application/json',
            ],
        ), to: Request::class);

        $this->assertEquals(json_encode(['foo' => 'bar']), $request->raw);
        $this->assertEquals(['foo' => 'bar'], $request->body);
    }

    public function test_files(): void
    {
        $currentPath = __DIR__ . '/Fixtures/upload-current.txt';

        copy(__DIR__ . '/Fixtures/upload.txt', $currentPath);

        $mapper = new PsrRequestToGenericRequestMapper();

        /** @var GenericRequest $request */
        $request = $mapper->map(
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
}
