<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use Laminas\Diactoros\UploadedFile;
use Tempest\Mapper\Exceptions\MissingValuesException;
use Tempest\Router\GenericRequest;
use Tempest\Router\Mappers\PsrRequestToRequestMapper;
use Tempest\Router\Request;
use Tempest\Router\Upload;
use Tests\Tempest\Fixtures\Modules\Books\Requests\CreateBookRequest;
use Tests\Tempest\Fixtures\Modules\Posts\PostRequest;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\Support\arr;

/**
 * @internal
 */
final class PsrRequestToRequestMapperTest extends FrameworkIntegrationTestCase
{
    public function test_can_map(): void
    {
        $mapper = new PsrRequestToRequestMapper();

        $this->assertTrue($mapper->canMap(from: $this->http->makePsrRequest('/'), to: PostRequest::class));
        $this->assertFalse($mapper->canMap(from: $this->http->makePsrRequest('/'), to: self::class));
    }

    public function test_map_with(): void
    {
        $mapper = new PsrRequestToRequestMapper();

        $request = $mapper->map(
            from: $this->http->makePsrRequest(
                uri: '/',
                body: ['title' => 'a', 'text' => 'b'],
                headers: ['x-test' => 'test'],
            ),
            to: PostRequest::class,
        );

        $this->assertInstanceOf(PostRequest::class, $request);
        $this->assertEquals('a', $request->title);
        $this->assertEquals('b', $request->text);
        $this->assertEquals(['x-test' => 'test'], $request->headers);
    }
    
    public function test_empty_strings_are_converted_to_null(): void
    {
        $mapper = new PsrRequestToRequestMapper();

        /** @var PostRequest $request */
        $request = $mapper->map(
            from: $this->http->makePsrRequest(
                uri: '/',
                body: ['title' => 'a', 'text' => ''],
            ),
            to: PostRequest::class,
        );

        $this->assertNull($request->text);
    }

    public function test_map_with_with_missing_data(): void
    {
        $this->expectException(MissingValuesException::class);

        $mapper = new PsrRequestToRequestMapper();

        try {
            $mapper->map(
                from: $this->http->makePsrRequest('/'),
                to: PostRequest::class,
            );
        } catch (MissingValuesException $missingValuesException) {
            $this->assertStringContainsString('title', $missingValuesException->getMessage());
            $this->assertStringContainsString('text', $missingValuesException->getMessage());

            throw $missingValuesException;
        }
    }

    public function test_generic_request_is_used_when_interface_is_passed(): void
    {
        $mapper = new PsrRequestToRequestMapper();

        $request = $mapper->map(
            from: $this->http->makePsrRequest('/'),
            to: Request::class,
        );

        $this->assertInstanceOf(GenericRequest::class, $request);
    }

    public function test_files(): void
    {
        $currentPath = __DIR__ . '/Fixtures/upload-current.txt';

        copy(__DIR__ . '/Fixtures/upload.txt', $currentPath);

        $mapper = new PsrRequestToRequestMapper();

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

    public function test_map_upload_file_into_request_property(): void
    {
        $currentPath = __DIR__ . '/Fixtures/upload.txt';

        $mapper = new PsrRequestToRequestMapper();

        $psrRequest = $this->http->makePsrRequest(
            uri: '/books',
            body: ['title' => 'Timeline Taxi'],
            files: ['cover' => new UploadedFile(
                streamOrFile: $currentPath,
                size: null,
                errorStatus: UPLOAD_ERR_OK,
            )],
        );

        $request = $mapper->map(
            from: $psrRequest,
            to: CreateBookRequest::class,
        );

        $this->assertInstanceOf(CreateBookRequest::class, $request);
        $this->assertInstanceOf(Upload::class, $request->cover);

        $this->assertEquals('cover', array_key_first($request->files));
        $this->assertTrue(arr($request->files)->isAssociative());
    }
}
