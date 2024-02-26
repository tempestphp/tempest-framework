<?php

declare(strict_types=1);

namespace Tests\Tempest\Testing\Http;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tempest\Http\GenericResponse;
use Tempest\Http\Status;
use Tempest\Testing\Http\TestResponseHelper;

class TestResponseHelperTest extends TestCase
{
    public function test_get_response()
    {
        $response = new GenericResponse(status: Status::OK);
        $helper = new TestResponseHelper($response);

        $this->assertSame($response, $helper->getResponse());
    }

    public function test_assert_has_header()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::OK,
                headers: ['Location' => '/new-location']
            )
        );

        $helper->assertHasHeader('Location');
    }

    public function test_assert_has_header_failure()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::OK
            )
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertHasHeader('Location');
    }

    public function test_assert_header_value_equals()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::OK,
                headers: ['Content-Type' => 'application/json']
            )
        );

        $helper->assertHeaderValueEquals('Content-Type', 'application/json');
    }

    public function test_assert_header_value_equals_failure()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK)
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertHeaderValueEquals('Content-Type', 'application/json');
    }

    public function test_assert_redirect()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::FOUND,
                headers: [
                    'Location' => '/other-location',
                ]
            )
        );

        $helper->assertRedirect();
    }

    public function test_assert_redirect_without_location_header()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::FOUND)
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertRedirect();
    }

    public function test_assert_redirect_without_3xx_status_code()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::OK,
                headers: ['Location' => '/other-location']
            )
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertRedirect();
    }

    public function test_assert_redirect_to()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(
                status: Status::FOUND,
                headers: ['Location' => '/other-location']
            )
        );

        $helper->assertRedirect('/other-location');
    }

    public function test_assert_ok()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK)
        );

        $helper->assertOk();
    }

    public function test_assert_ok_fails_with_not_okay_response()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::INTERNAL_SERVER_ERROR)
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertOk();
    }

    public function test_assert_not_found()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::NOT_FOUND)
        );

        $helper->assertNotFound();
    }

    public function test_assert_not_found_fails_with_okay_response()
    {
        $helper = new TestResponseHelper(
            new GenericResponse(status: Status::OK)
        );

        $this->expectException(AssertionFailedError::class);

        $helper->assertNotFound();
    }

    /**
     * @dataProvider assertStatusResponses
     */
    public function test_assert_status(Status $expectedStatus, GenericResponse $response)
    {
        $helper = new TestResponseHelper($response);

        $helper->assertStatus($expectedStatus);
    }

    /**
     * @dataProvider assertStatusResponsesFailing
     */
    public function test_assert_status_fails_when_status_does_not_match(Status $expectedStatus, GenericResponse $response)
    {
        $helper = new TestResponseHelper($response);

        $this->expectException(AssertionFailedError::class);

        $helper->assertStatus($expectedStatus);
    }

    public static function assertStatusResponses(): array
    {
        return [
            [Status::OK, new GenericResponse(status: Status::OK)],
            [Status::CREATED, new GenericResponse(status: Status::CREATED)],
            [Status::INTERNAL_SERVER_ERROR, new GenericResponse(status: Status::INTERNAL_SERVER_ERROR)],
        ];
    }

    public static function assertStatusResponsesFailing(): array
    {
        return [
            [Status::CREATED, new GenericResponse(status: Status::OK)],
            [Status::OK, new GenericResponse(status: Status::CREATED)],
            [Status::NOT_FOUND, new GenericResponse(status: Status::INTERNAL_SERVER_ERROR)],
        ];
    }
}
