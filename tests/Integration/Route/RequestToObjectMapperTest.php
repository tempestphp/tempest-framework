<?php

namespace Tests\Tempest\Integration\Route;

use Laminas\Diactoros\UploadedFile;
use Tempest\Http\Method;
use Tempest\Router\GenericRequest;
use Tempest\Router\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Router\Mappers\RequestToObjectMapper;
use Tempest\Router\Upload;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rules\NotNull;
use Tests\Tempest\Fixtures\Modules\Books\Requests\CreateBookRequest;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\RequestObjectA;
use function Tempest\map;
use function Tempest\Support\arr;

final class RequestToObjectMapperTest extends FrameworkIntegrationTestCase
{
    public function test_request(): void
    {
        $request = new GenericRequest(method: Method::POST, uri: '/', body: []);

        try {
            map($request)->to(RequestObjectA::class);
        } catch (ValidationException $validationException) {
            $this->assertInstanceOf(NotNull::class, $validationException->failingRules['b'][0]);
        }
    }

    public function test_files_are_mapped_to_properties(): void
    {
        $currentPath = __DIR__ . '/Fixtures/upload.txt';

        $request = map($this->http->makePsrRequest(
            uri: '/books',
            body: ['title' => 'Timeline Taxi'],
            files: ['cover' => new UploadedFile(
                streamOrFile: $currentPath,
                size: null,
                errorStatus: UPLOAD_ERR_OK,
            )],
        ))->with(
            PsrRequestToGenericRequestMapper::class,
            RequestToObjectMapper::class,
        )->to(CreateBookRequest::class);

        $this->assertInstanceOf(Upload::class, $request->cover);
        $this->assertEquals('cover', array_key_first($request->files));
        $this->assertTrue(arr($request->files)->isAssociative());
    }
    
    public function test_query_parameters_are_mapped_to_properties(): void
    {
        $request = map(new GenericRequest(
            method: Method::GET,
            uri: '/books?queryParam=hello',
            body: ['title' => 'Timeline Taxi'],
        ))->with(
            RequestToObjectMapper::class,
        )->to(CreateBookRequest::class);

        $this->assertSame('hello', $request->queryParam);
    }
}