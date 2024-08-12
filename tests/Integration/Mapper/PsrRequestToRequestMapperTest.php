<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\PsrRequestToRequestMapper;
use Tempest\Http\Request;
use Tempest\Mapper\Exceptions\MissingValuesException;
use Tests\Tempest\Fixtures\Modules\Posts\PostRequest;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class PsrRequestToRequestMapperTest extends FrameworkIntegrationTestCase
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
        $this->assertEquals(['x-test' => 'test'], $request->getHeaders());
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
}
