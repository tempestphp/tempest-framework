<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use App\Modules\Posts\PostRequest;
use Tempest\Http\GenericRequest;
use Tempest\Http\Request;
use Tempest\Mapper\RequestToRequestMapper;
use Tempest\ORM\Exceptions\MissingValuesException;
use function Tempest\request;
use Tempest\Testing\IntegrationTest;

class RequestToRequestMapperTest extends IntegrationTest
{
    /** @test */
    public function test_can_map()
    {
        $mapper = new RequestToRequestMapper();

        $this->assertTrue($mapper->canMap(PostRequest::class, request('/')));
        $this->assertFalse($mapper->canMap(self::class, request('/')));
    }

    /** @test */
    public function test_map_with()
    {
        $mapper = new RequestToRequestMapper();

        $request = $mapper->map(PostRequest::class, request('/', ['title' => 'a', 'text' => 'b']));

        $this->assertInstanceOf(PostRequest::class, $request);
    }

    /** @test */
    public function test_map_with_with_missing_data()
    {
        $this->expectException(MissingValuesException::class);

        $mapper = new RequestToRequestMapper();

        try {
            $mapper->map(PostRequest::class, request('/'));
        } catch (MissingValuesException $exception) {
            $this->assertStringContainsString('title', $exception->getMessage());
            $this->assertStringContainsString('text', $exception->getMessage());

            throw $exception;
        }
    }

    /** @test */
    public function generic_request_is_used_when_interface_is_passed()
    {
        $mapper = new RequestToRequestMapper();

        $request = $mapper->map(Request::class, request('/'));

        $this->assertInstanceOf(GenericRequest::class, $request);
    }
}
