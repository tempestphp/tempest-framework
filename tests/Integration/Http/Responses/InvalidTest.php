<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Validation\Rules\IsNotEmptyString;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class InvalidTest extends FrameworkIntegrationTestCase
{
    public function test_invalid_with_psr_request(): void
    {
        $request = new GenericRequest(Method::GET, '/original', ['foo' => 'bar'], ['referer' => '/original']);

        $response = new Invalid(
            $request,
            [
                'foo' => [
                    new IsNotEmptyString(),
                ],
            ],
        );

        $this->assertSame(Status::FOUND, $response->status);
        $this->assertSame('/original', $response->getHeader('Location')->values[0]);

        $session = $this->container->get(Session::class);

        $errors = $session->get(Session::VALIDATION_ERRORS);
        $originals = $session->get(Session::ORIGINAL_VALUES);

        $this->assertArrayHasKey('default', $errors);
        $this->assertArrayHasKey('foo', $errors['default']);
        $this->assertArrayHasKey('default', $originals);
        $this->assertArrayHasKey('foo', $originals['default']);
    }

    public function test_invalid_with_request(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/original',
            body: ['foo' => 'bar'],
            headers: ['referer' => '/original'],
        );

        $response = new Invalid(
            $request,
            [
                'foo' => [
                    new IsNotEmptyString(),
                ],
            ],
        );

        $this->assertSame(Status::FOUND, $response->status);
        $this->assertSame('/original', $response->getHeader('Location')->values[0]);

        $session = $this->container->get(Session::class);

        $errors = $session->get(Session::VALIDATION_ERRORS);
        $originals = $session->get(Session::ORIGINAL_VALUES);

        $this->assertArrayHasKey('default', $errors);
        $this->assertArrayHasKey('foo', $errors['default']);
        $this->assertArrayHasKey('default', $originals);
        $this->assertArrayHasKey('foo', $originals['default']);
    }
}
