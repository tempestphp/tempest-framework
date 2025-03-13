<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use Laminas\Diactoros\UploadedFile;
use Tempest\Mapper\Exceptions\MissingValuesException;
use Tempest\Router\GenericRequest;
use Tempest\Router\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Router\Request;
use Tempest\Router\Upload;
use Tests\Tempest\Fixtures\Modules\Books\Requests\CreateBookRequest;
use Tests\Tempest\Fixtures\Modules\Posts\PostRequest;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\map;
use function Tempest\Support\arr;

/**
 * @internal
 */
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
