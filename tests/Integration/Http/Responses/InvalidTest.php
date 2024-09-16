<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Method;
use Tempest\Http\Status;
use function Tempest\map;
use Tempest\Router\GenericRequest;
use Tempest\Router\Mappers\RequestToPsrRequestMapper;
use Tempest\Router\Responses\Invalid;
use Tempest\Router\Session\Session;
use Tempest\Validation\Rules\NotEmpty;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class InvalidTest extends FrameworkIntegrationTestCase
{
    public function test_invalid(): void
    {
        $request = map(new GenericRequest(Method::GET, '/original', ['foo' => 'bar']))->with(RequestToPsrRequestMapper::class);

        $response = new Invalid(
            $request,
            [
                'foo' => [
                    new NotEmpty(),
                ],
            ]
        );

        $this->assertSame(Status::FOUND, $response->getStatus());
        $this->assertSame('/original', $response->getHeader('Location')->values[0]);

        $session = $this->container->get(Session::class);

        $this->assertArrayHasKey('foo', $session->get(Session::VALIDATION_ERRORS));
        $this->assertArrayHasKey('foo', $session->get(Session::ORIGINAL_VALUES));
    }
}
