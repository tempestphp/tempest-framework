<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Method;
use Tempest\Http\Responses\Invalid;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use function Tempest\map;
use Tempest\Validation\Rules\NotEmpty;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class InvalidTest extends FrameworkIntegrationTestCase
{
    public function test_invalid(): void
    {
        /** @var PsrRequest $request */
        $request = map(new GenericRequest(Method::GET, '/original', ['foo' => 'bar']))->with(RequestToPsrRequestMapper::class);
        $request = $request->withHeader('Referer', '/original');

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
