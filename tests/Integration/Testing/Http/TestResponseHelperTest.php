<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Testing\Http;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Framework\Testing\Http\TestResponseHelper;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\Method;
use Tempest\Http\Status;

/**
 * @internal
 */
final class TestResponseHelperTest extends TestCase
{
    public function test_get_response(): void
    {
        $response = new GenericResponse(status: Status::OK);
        $helper = new TestResponseHelper($response, new GenericRequest(Method::GET, '/'));

        $this->assertSame($response, $helper->response);
    }

    public function test_assert_has_header(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::OK,
                headers: ['Location' => '/new-location'],
            ),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertHasHeader('Location');
    }

    public function test_assert_has_header_failure(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK),
            new GenericRequest(Method::GET, '/'),
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertHasHeader('Location');
    }

    public function test_assert_header_value_equals(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::OK,
                headers: ['Content-Type' => ['application/json']],
            ),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertHeaderContains('Content-Type', 'application/json');
    }

    public function test_assert_header_value_equals_failure(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK),
            new GenericRequest(Method::GET, '/'),
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertHeaderContains('Content-Type', 'application/json');
    }

    public function test_assert_redirect(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::FOUND,
                headers: [
                    'Location' => ['/other-location'],
                ],
            ),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertRedirect();
    }

    public function test_assert_redirect_without_location_header(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::FOUND),
            new GenericRequest(Method::GET, '/'),
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertRedirect();
    }

    public function test_assert_redirect_without_3xx_status_code(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::OK,
                headers: ['Location' => '/other-location'],
            ),
            new GenericRequest(Method::GET, '/'),
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertRedirect();
    }

    public function test_assert_redirect_to(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::FOUND,
                headers: ['Location' => ['/other-location']],
            ),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertRedirect('/other-location');
    }

    public function test_assert_ok(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertOk();
    }

    public function test_assert_ok_fails_with_not_okay_response(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::INTERNAL_SERVER_ERROR),
            new GenericRequest(Method::GET, '/'),
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertOk();
    }

    public function test_assert_not_found(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::NOT_FOUND),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertNotFound();
    }

    public function test_assert_not_found_fails_with_okay_response(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK),
            new GenericRequest(Method::GET, '/'),
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertNotFound();
    }

    #[DataProvider('provide_assert_status_cases')]
    public function test_assert_status(Status $expectedStatus, GenericResponse $response): void
    {
        $helper = new TestResponseHelper($response, new GenericRequest(Method::GET, '/'));

        $helper->assertStatus($expectedStatus);
    }

    #[DataProvider('provide_assert_status_fails_when_status_does_not_match_cases')]
    public function test_assert_status_fails_when_status_does_not_match(Status $expectedStatus, GenericResponse $response): void
    {
        $helper = new TestResponseHelper($response, new GenericRequest(Method::GET, '/'));

        $this->expectException(AssertionFailedError::class);

        $helper->assertStatus($expectedStatus);
    }

    public function test_assert_json_has_keys(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK, body: ['title' => 'Timeline Taxi', 'author' => ['name' => 'John']]),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertJsonHasKeys('title', 'author.name');
    }

    public function test_assert_json_contains(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK, body: ['title' => 'Timeline Taxi', 'author' => ['name' => 'John']]),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertJsonContains(['title' => 'Timeline Taxi']);
        $helper->assertJsonContains(['author' => ['name' => 'John']]);
        $helper->assertJsonContains(['author.name' => 'John']);
    }

    public function test_assert_json(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK, body: ['title' => 'Timeline Taxi', 'author' => ['name' => 'John']]),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertJson(['title' => 'Timeline Taxi', 'author' => ['name' => 'John']]);
        $helper->assertJson(['title' => 'Timeline Taxi', 'author.name' => 'John']);
    }

    public function test_assert_json_subset(): void
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK, body: ['title' => 'Timeline Taxi', 'author' => ['name' => 'John', 'country' => 'NL']]),
            new GenericRequest(Method::GET, '/'),
        );

        $helper->assertJsonSubset(['title' => 'Timeline Taxi', 'author' => ['country' => 'NL']]);
        $helper->assertJsonSubset(['title' => 'Timeline Taxi', 'author.country' => 'NL']);
    }

    public static function provide_assert_status_cases(): iterable
    {
        return [
            [Status::OK, new GenericResponse(status: Status::OK)],
            [Status::CREATED, new GenericResponse(status: Status::CREATED)],
            [Status::INTERNAL_SERVER_ERROR, new GenericResponse(status: Status::INTERNAL_SERVER_ERROR)],
        ];
    }

    public static function provide_assert_status_fails_when_status_does_not_match_cases(): iterable
    {
        return [
            [Status::CREATED, new GenericResponse(status: Status::OK)],
            [Status::OK, new GenericResponse(status: Status::CREATED)],
            [Status::NOT_FOUND, new GenericResponse(status: Status::INTERNAL_SERVER_ERROR)],
        ];
    }
}
