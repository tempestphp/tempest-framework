<?php

namespace Tests\Tempest\Integration\Route;

use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\PsrRequestToGenericRequestMapper;
use Tempest\Http\Mappers\RequestToObjectMapper;
use Tempest\Http\Method;
use Tempest\Http\RequestParametersIncludedReservedNames;
use Tempest\Http\Upload;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\Rules\IsNotNull;
use Tests\Tempest\Fixtures\Modules\Books\Requests\CreateBookRequest;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\EnumForRequest;
use Tests\Tempest\Integration\Route\Fixtures\RequestObjectA;
use Tests\Tempest\Integration\Route\Fixtures\RequestWithEnum;
use Tests\Tempest\Integration\Route\Fixtures\RequestWithOptionalProperties;
use Tests\Tempest\Integration\Route\Fixtures\RequestWithTypedQueryParam;

use function Tempest\Mapper\map;
use function Tempest\Support\arr;

final class RequestToObjectMapperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function request(): void
    {
        $request = new GenericRequest(method: Method::POST, uri: '/', body: []);

        try {
            map($request)->to(RequestObjectA::class);
        } catch (ValidationFailed $validationFailed) {
            $this->assertInstanceOf(IsNotNull::class, $validationFailed->failingRules['b'][0]->rule);
        }
    }

    #[Test]
    public function files_are_mapped_to_properties(): void
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

    #[Test]
    public function query_parameters_are_mapped_to_properties(): void
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

    #[Test]
    public function query_params_with_types(): void
    {
        $request = map(new GenericRequest(
            method: Method::GET,
            uri: '/books?stringParam=a&intParam=1&floatParam=0.1&boolParam=1',
            body: ['title' => 'Timeline Taxi'],
        ))->with(
            RequestToObjectMapper::class,
        )->to(RequestWithTypedQueryParam::class);

        $this->assertSame(1, $request->intParam);
        $this->assertSame('a', $request->stringParam);
        $this->assertTrue($request->boolParam);
        $this->assertSame(0.1, $request->floatParam);
    }

    #[Test]
    public function mapping_with_enum(): void
    {
        $request = map(new GenericRequest(
            method: Method::GET,
            uri: '/books?enumParam=bar',
        ))->with(
            RequestToObjectMapper::class,
        )->to(RequestWithEnum::class);

        $this->assertSame(EnumForRequest::BAR, $request->enumParam);
    }

    #[Test]
    public function validation_fails_for_enum(): void
    {
        try {
            map(new GenericRequest(
                method: Method::GET,
                uri: '/books?enumParam=unknown',
            ))->with(
                RequestToObjectMapper::class,
            )->to(RequestWithEnum::class);
        } catch (ValidationFailed $validationFailed) {
            $this->assertArrayHasKey('enumParam', $validationFailed->failingRules);
        }
    }

    #[Test]
    public function reserved_properties_cannot_be_mapped(): void
    {
        $this->assertException(
            expectedExceptionClass: RequestParametersIncludedReservedNames::class,
            handler: function (): void {
                map(new GenericRequest(
                    method: Method::GET,
                    uri: '/books?uri=invalid',
                    body: ['query' => 'invalid'],
                    files: ['body' => 'invalid'],
                ))->with(
                    RequestToObjectMapper::class,
                )->to(RequestForInvalidMap::class);
            },
            assertException: function (RequestParametersIncludedReservedNames $exception): void {
                $this->assertStringContainsString('uri', $exception->getMessage());
                $this->assertStringContainsString('query', $exception->getMessage());
                $this->assertStringContainsString('body', $exception->getMessage());
            },
        );
    }

    #[Test]
    public function missing_enum_value(): void
    {
        $request = new GenericRequest(method: Method::POST, uri: '/', body: []);

        try {
            map($request)->to(RequestWithEnum::class);
        } catch (ValidationFailed $validationFailed) {
            $this->assertInstanceOf(IsNotNull::class, $validationFailed->failingRules['enumParam'][0]->rule);
        }
    }

    #[Test]
    public function missing_optional_properties_are_not_initialized(): void
    {
        $request = map(new GenericRequest(
            method: Method::PATCH,
            uri: '/',
            body: ['expiryDate' => null],
        ))->with(
            RequestToObjectMapper::class,
        )->to(RequestWithOptionalProperties::class);

        $this->assertFalse(new ReflectionProperty($request, 'title')->isInitialized($request));
        $this->assertTrue(new ReflectionProperty($request, 'expiryDate')->isInitialized($request));
        $this->assertNull($request->expiryDate);
    }

    #[Test]
    public function present_optional_properties_are_validated(): void
    {
        $this->assertException(
            expectedExceptionClass: ValidationFailed::class,
            handler: fn () => map(new GenericRequest(
                method: Method::PATCH,
                uri: '/',
                body: ['title' => ''],
            ))->with(
                RequestToObjectMapper::class,
            )->to(RequestWithOptionalProperties::class),
            assertException: fn (ValidationFailed $exception) => $this->assertArrayHasKey('title', $exception->failingRules),
        );
    }
}
